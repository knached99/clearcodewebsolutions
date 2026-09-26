<?php

namespace App\Livewire\Teams\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessageAttachment;
use App\Notifications\NewTicketReplyNotification;
use App\Notifications\PaymentLinkNotification;
use App\Notifications\TicketPriorityChangedNotification;
use App\Notifications\TicketStatusChangedNotification;
use App\Services\StripeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Show extends Component
{
    use WithFileUploads;

    /*
    |--------------------------------------------------------------------------
    | Ticket
    |--------------------------------------------------------------------------
    */

    public Ticket $ticket;

    /*
    |--------------------------------------------------------------------------
    | Ticket State
    |--------------------------------------------------------------------------
    */

    public string $status = '';

    public string $priority = '';

    /*
    |--------------------------------------------------------------------------
    | New Message
    |--------------------------------------------------------------------------
    */

    public string $newMessage = '';

    public array $attachments = [];

    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    */

    private const MAX_ATTACHMENTS = 10;

    private const MAX_ATTACHMENT_SIZE_KB = 10240;

    private const MAX_TOTAL_ATTACHMENT_SIZE_KB = 51200;

    private const MAX_MESSAGE_LENGTH = 10000;

    private const MESSAGE_RATE_LIMIT = 20;

    private const MESSAGE_RATE_LIMIT_SECONDS = 300;

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(Ticket $ticket): void
    {
        /*
         * Explicit authorization.
         *
         * If your TicketPolicy already defines `view`, this protects
         * against somebody manually changing the ticket ID in the URL.
         */
        $this->authorize('view', $ticket);

        $this->ticket = $ticket;

        $this->syncTicketState();

        $this->loadTicket();
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(string $value): void
    {
        $this->authorize('update', $this->ticket);

        $value = strtolower(trim($value));

        validator(
            ['status' => $value],
            [
                'status' => [
                    'required',
                    'in:open,pending,resolved,closed',
                ],
            ],
        )->validate();

        /*
         * Re-fetch the current database state.
         *
         * Livewire's serialized model can be stale if another staff
         * member changed the ticket while this page was open.
         */
        $ticket = Ticket::query()
            ->find($this->ticket->id);

        if (! $ticket) {
            abort(404);
        }

        $this->authorize('update', $ticket);

        /*
         * Nothing changed.
         */
        if ($value === $ticket->status) {
            $this->ticket = $ticket;

            $this->syncTicketState();

            return;
        }

        $oldStatus = (string) $ticket->status;

        /*
         * Use a transaction + row lock so two simultaneous staff actions
         * cannot overwrite each other's state.
         */
        $updatedTicket = DB::transaction(
            function () use (
                $ticket,
                $value,
            ): Ticket {
                $lockedTicket = Ticket::query()
                    ->whereKey($ticket->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedTicket) {
                    abort(404);
                }

                $lockedTicket->forceFill([
                    'status' => $value,
                    'last_activity_at' => now(),
                ])->save();

                return $lockedTicket;
            },
        );

        /*
         * Update the component without another refresh query.
         */
        $this->ticket = $updatedTicket;

        $this->syncTicketState();

        /*
         * Synchronous email is intentional, but it happens AFTER the
         * transaction has committed.
         *
         * A slow SMTP connection therefore does not hold DB locks open.
         */
        try {
            Notification::route(
                'mail',
                $updatedTicket->email,
            )->notify(
                new TicketStatusChangedNotification(
                    $updatedTicket,
                    $oldStatus,
                    $updatedTicket->status,
                ),
            );
        } catch (Throwable $exception) {
            /*
             * The status change succeeded. Do not undo it because email
             * delivery failed.
             */
            report($exception);

            session()->flash(
                'ticket-warning',
                'The ticket status was updated, but the customer notification could not be sent.',
            );
        }

        $this->dispatch('ticket-status-updated');
    }

    /*
    |--------------------------------------------------------------------------
    | Priority
    |--------------------------------------------------------------------------
    */

    public function updatePriority(string $value): void
    {
        $this->authorize('update', $this->ticket);

        $value = strtolower(trim($value));

        validator(
            ['priority' => $value],
            [
                'priority' => [
                    'required',
                    'in:low,normal,high,urgent',
                ],
            ],
        )->validate();

        /*
         * Re-fetch the current ticket.
         */
        $ticket = Ticket::query()
            ->find($this->ticket->id);

        if (! $ticket) {
            abort(404);
        }

        $this->authorize('update', $ticket);

        /*
         * Nothing changed.
         */
        if ($value === $ticket->priority) {
            $this->ticket = $ticket;

            $this->syncTicketState();

            return;
        }

        $oldPriority = (string) $ticket->priority;

        /*
         * Atomic update with row lock.
         */
        $updatedTicket = DB::transaction(
            function () use (
                $ticket,
                $value,
            ): Ticket {
                $lockedTicket = Ticket::query()
                    ->whereKey($ticket->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedTicket) {
                    abort(404);
                }

                $lockedTicket->forceFill([
                    'priority' => $value,
                    'last_activity_at' => now(),
                ])->save();

                return $lockedTicket;
            },
        );

        $this->ticket = $updatedTicket;

        $this->syncTicketState();

        /*
         * Synchronous email AFTER transaction commit.
         */
        try {
            Notification::route(
                'mail',
                $updatedTicket->email,
            )->notify(
                new TicketPriorityChangedNotification(
                    $updatedTicket,
                    $oldPriority,
                    $updatedTicket->priority,
                ),
            );
        } catch (Throwable $exception) {
            report($exception);

            session()->flash(
                'ticket-warning',
                'The priority was updated, but the customer notification could not be sent.',
            );
        }

        $this->dispatch('ticket-priority-updated');
    }

    /*
    |--------------------------------------------------------------------------
    | Send Message
    |--------------------------------------------------------------------------
    */

    public function sendMessage(): void
    {
        $this->authorize('update', $this->ticket);

        /*
         * Normalize before validation.
         */
        $this->newMessage = trim($this->newMessage);

        /*
         * Validate the message and uploads.
         */
        $this->validate([
            'newMessage' => [
                'required',
                'string',
                'min:1',
                'max:'.self::MAX_MESSAGE_LENGTH,
            ],

            'attachments' => [
                'nullable',
                'array',
                'max:'.self::MAX_ATTACHMENTS,
            ],

            'attachments.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:'.self::MAX_ATTACHMENT_SIZE_KB,
            ],
        ], [
            'newMessage.required' => 'Please enter a message.',
            'newMessage.max' => 'Your message may not exceed 10,000 characters.',
            'attachments.max' => 'You may attach up to 10 files.',
            'attachments.*.image' => 'Each attachment must be a valid image.',
            'attachments.*.mimes' => 'Only JPG, JPEG, PNG, WEBP, and GIF images are allowed.',
            'attachments.*.max' => 'Each attachment may not exceed 10 MB.',
        ]);

        /*
         * Validate the aggregate upload size.
         */
        $this->validateTotalAttachmentSize();

        /*
         * Re-fetch the ticket so the operation is based on current
         * database state rather than a potentially stale Livewire model.
         */
        $ticket = Ticket::query()
            ->find($this->ticket->id);

        if (! $ticket) {
            abort(404);
        }

        $this->authorize('update', $ticket);

        /*
         * Rate-limit support message sends.
         *
         * This protects against a browser loop/double-click generating
         * a large number of synchronous customer emails.
         */
        $userId = auth()->id();

        if (! $userId) {
            abort(403);
        }

        $rateLimitKey = sprintf(
            'support-ticket-message:%s:%s',
            $userId,
            $ticket->id,
        );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                self::MESSAGE_RATE_LIMIT,
            )
        ) {
            $seconds = RateLimiter::availableIn(
                $rateLimitKey,
            );

            $this->addError(
                'newMessage',
                'Too many messages have been sent. Please try again in '
                .$this->formatRateLimitTime($seconds)
                .'.',
            );

            return;
        }

        RateLimiter::hit(
            $rateLimitKey,
            self::MESSAGE_RATE_LIMIT_SECONDS,
        );

        /*
         * Store uploads BEFORE the transaction.
         *
         * File I/O can be slow. Keeping it outside the transaction means
         * database locks are held for the shortest possible time.
         */
        $storedFiles = [];

        try {
            foreach ($this->attachments as $attachment) {
                $storedFiles[] = $this->storeAttachment(
                    $attachment,
                    $ticket->id,
                );
            }

            /*
             * Short database transaction.
             *
             * No file uploads.
             * No email.
             */
            $message = DB::transaction(
                function () use (
                    $ticket,
                    $storedFiles,
                ) {
                    $lockedTicket = Ticket::query()
                        ->whereKey($ticket->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedTicket) {
                        abort(404);
                    }

                    /*
                     * Create the support reply.
                     */
                    $message = $lockedTicket
                        ->messages()
                        ->create([
                            'author_type' => 'support',
                            'author_name' => auth()->user()->name,
                            'message' => $this->newMessage,
                        ]);

                    /*
                     * Create attachment records.
                     */
                    foreach ($storedFiles as $storedFile) {
                        $message->attachments()->create([
                            'original_name' => $storedFile['original_name'],
                            'path' => $storedFile['path'],
                            'mime_type' => $storedFile['mime_type'],
                            'size' => $storedFile['size'],
                        ]);
                    }

                    /*
                     * Update ticket activity.
                     */
                    $lockedTicket->forceFill([
                        'last_activity_at' => now(),
                    ])->save();

                    return $message;
                },
            );
        } catch (ValidationException $exception) {
            /*
             * Database operation failed after files were stored.
             * Remove the orphaned files.
             */
            $this->deleteStoredFiles($storedFiles);

            throw $exception;
        } catch (Throwable $exception) {
            /*
             * Clean up filesystem state if DB work fails.
             */
            $this->deleteStoredFiles($storedFiles);

            report($exception);

            $this->addError(
                'newMessage',
                'The message could not be sent. Please try again.',
            );

            return;
        }

        /*
         * ------------------------------------------------------------------
         * ADMIN -> CUSTOMER EMAIL
         * ------------------------------------------------------------------
         *
         * Synchronous by design.
         *
         * But critically, the database transaction has already committed.
         */
        try {
            Notification::route(
                'mail',
                $ticket->email,
            )->notify(
                new NewTicketReplyNotification(
                    $ticket,
                    $message,
                    false,
                ),
            );
        } catch (Throwable $exception) {
            /*
             * The support reply is already safely stored.
             *
             * Do not remove it because SMTP failed.
             */
            report($exception);

            session()->flash(
                'ticket-warning',
                'The message was saved, but the customer notification could not be sent.',
            );
        }

        /*
         * Clear form.
         */
        $this->reset([
            'newMessage',
            'attachments',
        ]);

        /*
         * Reload current ticket state.
         */
        $this->ticket = $ticket
            ->fresh()
            ->load([
                'messages.attachments',
            ]);

        $this->syncTicketState();

        $this->dispatch('message-sent');
    }

    /*
    |--------------------------------------------------------------------------
    | Send Payment Link
    |--------------------------------------------------------------------------
    */

    public function sendPaymentLink(StripeService $stripe, int $amountInCents = 10000): void
    {
        Log::info('[PaymentLink] Process started.', [
            'ticket_id' => $this->ticket->id ?? null,
            'recipient_email' => $this->ticket->email ?? null,
            'amount_in_cents' => $amountInCents,
            'triggered_by_user_id' => auth()->id(),
        ]);

        $this->authorize('update', $this->ticket);

        try {
            // 1. Resolve Stripe Customer
            Log::info('[PaymentLink] Resolving Stripe customer...', [
                'email' => $this->ticket->email,
                'name' => $this->ticket->name ?? 'Customer',
            ]);

            $customer = $stripe->resolveCustomer(
                $this->ticket->email,
                $this->ticket->name ?? 'Customer'
            );

            Log::info('[PaymentLink] Stripe customer resolved.', [
                'customer_id' => $customer->id ?? null,
            ]);

            // 2. Create Stripe Product
            Log::info('[PaymentLink] Creating Stripe product...');

            $product = $stripe->createProduct(
                'Service Payment - Ticket #'.$this->ticket->id,
                'Payment request generated from support ticket.'
            );

            Log::info('[PaymentLink] Stripe product created.', [
                'product_id' => $product->id ?? null,
            ]);

            // 3. Create Stripe Price
            Log::info('[PaymentLink] Creating Stripe price...', [
                'product_id' => $product->id ?? null,
                'amount_in_cents' => $amountInCents,
            ]);

            $price = $stripe->createPrice($product->id, $amountInCents);

            Log::info('[PaymentLink] Stripe price created.', [
                'price_id' => $price->id ?? null,
            ]);

            // 4. Generate Checkout Link
            Log::info('[PaymentLink] Generating Stripe checkout session...');

            $session = $stripe->createCheckoutLink($customer->id, $price->id);

            Log::info('[PaymentLink] Stripe checkout link generated successfully.', [
                'checkout_url' => $session->url ?? null,
            ]);

            // 5. Send Mail Notification
            Log::info('[PaymentLink] Attempting to dispatch email notification...', [
                'recipient' => $this->ticket->email,
                'checkout_url' => $session->url ?? null,
            ]);

            Notification::route('mail', $this->ticket->email)
                ->notify(new PaymentLinkNotification($session->url));

            Log::info('[PaymentLink] Notification::notify() executed without raising an exception.');

            // 6. Add System Note to Ticket
            $this->ticket->messages()->create([
                'author_type' => 'system',
                'author_name' => 'Billing System',
                'message' => 'A secure payment link for $'.number_format($amountInCents / 100, 2).' was sent to the customer.',
            ]);

            Log::info('[PaymentLink] System message logged to ticket.');

            // 7. Reload State & UI
            $this->loadTicket();

            session()->flash(
                'ticket-success',
                'Payment link successfully generated and emailed to the customer.'
            );

            $this->dispatch('payment-link-sent');

            Log::info('[PaymentLink] Process completed successfully.');

        } catch (Throwable $exception) {
            Log::error('[PaymentLink] Execution failed with an exception.', [
                'ticket_id' => $this->ticket->id ?? null,
                'exception_message' => $exception->getMessage(),
                'exception_code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            report($exception);

            $this->addError(
                'stripeError',
                'There was an issue generating the payment link: '.$exception->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Upload Validation
    |--------------------------------------------------------------------------
    */

    public function updatedAttachments(): void
    {
        $this->validate([
            'attachments' => [
                'nullable',
                'array',
                'max:'.self::MAX_ATTACHMENTS,
            ],

            'attachments.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:'.self::MAX_ATTACHMENT_SIZE_KB,
            ],
        ]);

        $this->validateTotalAttachmentSize();
    }

    /*
    |--------------------------------------------------------------------------
    | Total Attachment Size
    |--------------------------------------------------------------------------
    */

    protected function validateTotalAttachmentSize(): void
    {
        if ($this->attachments === []) {
            return;
        }

        $totalBytes = 0;

        foreach ($this->attachments as $attachment) {
            $size = $attachment->getSize();

            if (is_int($size)) {
                $totalBytes += $size;
            }
        }

        $maxBytes =
            self::MAX_TOTAL_ATTACHMENT_SIZE_KB * 1024;

        if ($totalBytes > $maxBytes) {
            throw ValidationException::withMessages([
                'attachments' => 'The combined size of your attachments may not exceed 50 MB.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store Attachment
    |--------------------------------------------------------------------------
    */

    protected function storeAttachment(
        mixed $attachment,
        int $ticketId,
    ): array {
        $extension = strtolower(
            $attachment->getClientOriginalExtension(),
        );

        /*
         * Do not rely exclusively on the validation rule.
         *
         * Keep a server-side whitelist.
         */
        if (! in_array(
            $extension,
            ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            true,
        )) {
            throw ValidationException::withMessages([
                'attachments' => 'One of the uploaded files has an invalid file type.',
            ]);
        }

        /*
         * Generate a server-side filename/path.
         *
         * The client's filename is never used as the filesystem name.
         */
        $path = $attachment->store(
            'tickets/'.$ticketId,
            'public',
        );

        return [
            'path' => $path,

            'original_name' => $this->sanitizeOriginalFilename(
                $attachment->getClientOriginalName(),
            ),

            'mime_type' => $attachment->getMimeType(),

            'size' => $attachment->getSize(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Filename Sanitization
    |--------------------------------------------------------------------------
    */

    protected function sanitizeOriginalFilename(
        string $filename,
    ): string {
        /*
         * Prevent path traversal in the display/download filename.
         */
        $filename = basename($filename);

        /*
         * Remove control characters.
         */
        $filename = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            '',
            $filename,
        ) ?? '';

        /*
         * Normalize whitespace.
         */
        $filename = preg_replace(
            '/\s+/u',
            ' ',
            $filename,
        ) ?? '';

        $filename = trim($filename);

        if ($filename === '') {
            return 'attachment';
        }

        return mb_substr(
            $filename,
            0,
            255,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Stored Files
    |--------------------------------------------------------------------------
    */

    protected function deleteStoredFiles(
        array $storedFiles,
    ): void {
        foreach ($storedFiles as $storedFile) {
            if (
                ! isset($storedFile['path'])
                || ! is_string($storedFile['path'])
                || $storedFile['path'] === ''
            ) {
                continue;
            }

            try {
                Storage::disk('public')->delete(
                    $storedFile['path'],
                );
            } catch (Throwable $exception) {
                /*
                 * Do not hide the original database error.
                 */
                report($exception);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Attachment
    |--------------------------------------------------------------------------
    */

    public function removeAttachment(int $index): void
    {
        if (! array_key_exists(
            $index,
            $this->attachments,
        )) {
            return;
        }

        unset($this->attachments[$index]);

        $this->attachments = array_values(
            $this->attachments,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment Preview
    |--------------------------------------------------------------------------
    */

    public function preview(
        TicketMessageAttachment $attachment,
    ): StreamedResponse {
        $this->authorizeAttachment($attachment);

        $disk = Storage::disk('public');

        abort_unless(
            $disk->exists($attachment->path),
            404,
            'Attachment file not found.',
        );

        /*
         * Prefer the stored MIME type, but fall back to the filesystem
         * MIME type if necessary.
         */
        $mime = $attachment->mime_type
            ?: $disk->mimeType($attachment->path)
            ?: 'application/octet-stream';

        /*
         * Only allow known image MIME types to be rendered inline.
         *
         * This prevents an unexpected file type from being treated as
         * browser-renderable content.
         */
        $allowedInlineMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ];

        if (! in_array($mime, $allowedInlineMimes, true)) {
            abort(415, 'Attachment cannot be previewed.');
        }

        $filename = $this->sanitizeOriginalFilename(
            $attachment->original_name,
        );

        return response()->stream(
            function () use (
                $disk,
                $attachment,
            ) {
                $stream = $disk->readStream(
                    $attachment->path,
                );

                abort_unless(
                    $stream !== false,
                    404,
                    'Unable to read attachment.',
                );

                try {
                    fpassthru($stream);
                } finally {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $mime,

                /*
                 * RFC 5987-compatible filename handling is preferable
                 * to blindly inserting a customer-provided filename
                 * into a header.
                 */
                'Content-Disposition' => 'inline; filename="'.
                    addcslashes(
                        $filename,
                        '\\"',
                    ).
                    '"',

                'X-Content-Type-Options' => 'nosniff',

                'Cache-Control' => 'private, no-store, max-age=0',

                'Pragma' => 'no-cache',
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment Download
    |--------------------------------------------------------------------------
    */

    public function download(
        TicketMessageAttachment $attachment,
    ): StreamedResponse {
        $this->authorizeAttachment($attachment);

        $disk = Storage::disk('public');

        abort_unless(
            $disk->exists($attachment->path),
            404,
            'Attachment file not found.',
        );

        $filename = $this->sanitizeOriginalFilename(
            $attachment->original_name,
        );

        return $disk->download(
            $attachment->path,
            $filename,
            [
                'X-Content-Type-Options' => 'nosniff',

                'Cache-Control' => 'private, no-store, max-age=0',

                'Pragma' => 'no-cache',
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment Authorization
    |--------------------------------------------------------------------------
    */

    protected function authorizeAttachment(
        TicketMessageAttachment $attachment,
    ): void {
        /*
         * The support user must be authorized to view the ticket.
         */
        $this->authorize(
            'view',
            $this->ticket,
        );

        /*
         * Only load the relationship if it isn't already present.
         */
        $attachment->loadMissing('message');

        /*
         * Missing relationships or a mismatched ticket should return 404
         * rather than exposing information about another ticket.
         */
        abort_unless(
            $attachment->message
            && (int) $attachment->message->ticket_id ===
                (int) $this->ticket->id,
            404,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ticket State
    |--------------------------------------------------------------------------
    */

    protected function syncTicketState(): void
    {
        $this->status = (string) $this->ticket->status;

        $this->priority = (string) $this->ticket->priority;
    }

    /*
    |--------------------------------------------------------------------------
    | Reload Ticket
    |--------------------------------------------------------------------------
    */

    protected function loadTicket(): void
    {
        $this->ticket->load([
            'messages.attachments',
            'invoice.items',
            'invoice.transactions',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Message
    |--------------------------------------------------------------------------
    */

    protected function formatRateLimitTime(
        int $seconds,
    ): string {
        if ($seconds < 60) {
            return $seconds.' seconds';
        }

        $minutes = (int) ceil($seconds / 60);

        return $minutes
            .' minute'
            .($minutes === 1 ? '' : 's');
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.teams.tickets.show',
        );
    }
}
