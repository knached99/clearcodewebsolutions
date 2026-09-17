<?php

namespace App\Livewire\Home\Tickets;

use App\Models\Ticket;
use App\Notifications\NewTicketReplyNotification;
use App\Services\TicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class GuestTicket extends Component
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
    | Reply
    |--------------------------------------------------------------------------
    */

    public string $message = '';

    public array $attachments = [];

    /*
    |--------------------------------------------------------------------------
    | Ticket Editing
    |--------------------------------------------------------------------------
    |
    | These properties are kept because the existing view may bind to them.
    |
    */

    public bool $showEditTicket = false;

    public string $subject = '';

    public string $status = '';

    public string $priority = '';

    /*
    |--------------------------------------------------------------------------
    | Email Deep-Link
    |--------------------------------------------------------------------------
    */

    public ?int $highlightMessageId = null;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    private const MAX_ATTACHMENTS = 10;

    private const MAX_ATTACHMENT_SIZE_KB = 10240;

    private const MAX_TOTAL_ATTACHMENT_SIZE_KB = 51200;

    private const MAX_MESSAGE_LENGTH = 10000;

    private const REPLY_RATE_LIMIT = 5;

    private const REPLY_RATE_LIMIT_SECONDS = 300;

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        return [
            'message' => [
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
        ];
    }

    protected function messages(): array
    {
        return [
            'message.required' => 'Please enter a message.',

            'message.max' => 'Your message may not exceed 10,000 characters.',

            'attachments.max' => 'You may attach up to 10 files.',

            'attachments.*.image' => 'Each attachment must be a valid image.',

            'attachments.*.mimes' => 'Only JPG, JPEG, PNG, WEBP, and GIF images are allowed.',

            'attachments.*.max' => 'Each attachment may not exceed 10 MB.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(
        Ticket $ticket,
        TicketService $ticketService,
    ): void {
        /*
         * Guest access must be verified before loading ticket data.
         */
        $ticketService->requireGuestTicketAccess($ticket);

        /*
         * Email deep-link:
         *
         * /tickets/TCKT-XXXX/token?message=10
         */
        $this->highlightMessageId =
            request()->integer('message') ?: null;

        /*
         * Load the ticket and its messages once.
         */
        $this->ticket = $ticket->load([
            'messages.attachments',
        ]);

        $this->syncTicketProperties();
    }

    /*
    |--------------------------------------------------------------------------
    | Send Reply
    |--------------------------------------------------------------------------
    */

    public function sendReply(
        TicketService $ticketService,
    ): void {
        /*
         * Never trust the Livewire model instance for authorization.
         *
         * Re-fetch the ticket from the database because this component
         * can remain open in a browser while the ticket changes.
         */
        $ticket = Ticket::query()
            ->find($this->ticket->id);

        if (! $ticket) {
            abort(404);
        }

        /*
         * Verify guest access again on every state-changing request.
         */
        $ticketService->requireGuestTicketAccess($ticket);

        /*
         * Normalize the message before validation.
         */
        $this->message = trim($this->message);

        /*
         * Validate the reply and attachments.
         */
        $this->validate();

        /*
         * Explicitly reject whitespace-only messages.
         */
        if ($this->message === '') {
            $this->addError(
                'message',
                'Please enter a message.',
            );

            return;
        }

        /*
         * Protect the endpoint against rapid repeated replies.
         *
         * Use both the ticket ID and IP address so one guest cannot
         * accidentally or intentionally generate a large number of
         * synchronous notification emails.
         */
        $ipAddress = request()->ip() ?? 'unknown';

        $rateLimitKey = sprintf(
            'guest-ticket-reply:%s:%s',
            $ticket->id,
            $ipAddress,
        );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                self::REPLY_RATE_LIMIT,
            )
        ) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            $this->addError(
                'message',
                'Too many replies. Please try again in '
                .$this->formatRateLimitTime($seconds)
                .'.',
            );

            return;
        }

        /*
         * Count the attempt before performing database/storage/email work.
         *
         * This prevents an attacker from repeatedly triggering expensive
         * synchronous operations.
         */
        RateLimiter::hit(
            $rateLimitKey,
            self::REPLY_RATE_LIMIT_SECONDS,
        );

        /*
         * Validate the aggregate attachment size.
         *
         * The individual 10 MB rule alone allows up to 100 MB in a
         * single request, which is unnecessarily expensive on shared
         * hosting.
         */
        $this->validateTotalAttachmentSize();

        /*
         * Check whether the ticket is already closed before doing any
         * storage work.
         *
         * We check again inside the transaction using a row lock because
         * another request could close the ticket between this check and
         * the actual insert.
         */
        if ($ticket->status === 'closed') {
            $this->addError(
                'message',
                'This ticket is closed and can no longer receive replies.',
            );

            return;
        }

        /*
         * Store uploaded files before opening the database transaction.
         *
         * File storage is I/O and can be slow. We do not want the DB
         * transaction held open while the filesystem is being written.
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
             * Keep the transaction short.
             *
             * Most importantly:
             *
             * - no file uploads inside the transaction
             * - no email inside the transaction
             */
            $message = DB::transaction(function () use (
                $ticket,
                $storedFiles,
            ) {
                /*
                 * Lock the ticket row to prevent concurrent replies from
                 * racing with ticket closure/update operations.
                 */
                $lockedTicket = Ticket::query()
                    ->whereKey($ticket->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedTicket) {
                    abort(404);
                }

                /*
                 * Check the status again after acquiring the lock.
                 */
                if ($lockedTicket->status === 'closed') {
                    throw ValidationException::withMessages([
                        'message' => 'This ticket is closed and can no longer receive replies.',
                    ]);
                }

                /*
                 * Create the guest message.
                 */
                $message = $lockedTicket->messages()->create([
                    'author_type' => 'guest',
                    'author_name' => $lockedTicket->name,
                    'message' => $this->message,
                ]);

                /*
                 * Create attachment records.
                 */
                foreach ($storedFiles as $storedFile) {
                    $message->attachments()->create([
                        'path' => $storedFile['path'],
                        'original_name' => $storedFile['original_name'],
                        'mime_type' => $storedFile['mime_type'],
                        'size' => $storedFile['size'],
                    ]);
                }

                /*
                 * Update activity timestamp.
                 */
                $lockedTicket->forceFill([
                    'last_activity_at' => now(),
                ])->save();

                return $message;
            });
        } catch (ValidationException $exception) {
            /*
             * The files have already been stored, but the DB transaction
             * was rejected. Remove them so orphaned files are not left
             * behind.
             */
            $this->deleteStoredFiles($storedFiles);

            throw $exception;
        } catch (Throwable $exception) {
            /*
             * Clean up uploaded files if anything fails.
             */
            $this->deleteStoredFiles($storedFiles);

            report($exception);

            $this->addError(
                'message',
                'We could not send your reply. Please try again.',
            );

            return;
        }

        /*
         * ------------------------------------------------------------------
         * CUSTOMER -> ADMIN EMAIL
         * ------------------------------------------------------------------
         *
         * IMPORTANT:
         *
         * This remains synchronous by design.
         *
         * But it is deliberately OUTSIDE the database transaction.
         *
         * The database work has already committed, so SMTP latency cannot
         * keep database locks open.
         */
        try {
            Notification::route(
                'mail',
                config('mail.admin_address'),
            )->notify(
                new NewTicketReplyNotification(
                    $ticket,
                    $message,
                    true,
                ),
            );
        } catch (Throwable $exception) {
            /*
             * The reply itself was successfully stored.
             *
             * Do not roll back the customer's message because an external
             * mail server failed.
             */
            report($exception);

            session()->flash(
                'ticket-warning',
                'Your reply was saved, but we could not notify the support team by email.',
            );
        }

        /*
         * Refresh only after all database work is complete.
         */
        $this->ticket = $ticket
            ->fresh()
            ->load([
                'messages.attachments',
            ]);

        $this->syncTicketProperties();

        /*
         * Reset upload state.
         */
        $this->message = '';

        $this->attachments = [];

        /*
         * Success message.
         */
        session()->flash(
            'ticket-success',
            'Your reply has been sent.',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment Storage
    |--------------------------------------------------------------------------
    */

    protected function storeAttachment(
        mixed $attachment,
        int $ticketId,
    ): array {
        /*
         * Generate a server-side filename.
         *
         * Never use the customer's original filename as the actual
         * filesystem filename.
         */
        $extension = strtolower(
            $attachment->getClientOriginalExtension(),
        );

        /*
         * Whitelist the extension independently of the validation rule.
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

        $path = $attachment->store(
            'tickets/'.$ticketId,
            'public',
        );

        return [
            'path' => $path,

            /*
             * Store a safe display name instead of trusting arbitrary
             * filesystem input.
             */
            'original_name' => $this->sanitizeOriginalFilename(
                $attachment->getClientOriginalName(),
            ),

            /*
             * getMimeType() uses PHP's file inspection rather than trusting
             * the browser-provided Content-Type.
             */
            'mime_type' => $attachment->getMimeType(),

            'size' => $attachment->getSize(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment Filename Sanitization
    |--------------------------------------------------------------------------
    */

    protected function sanitizeOriginalFilename(
        string $filename,
    ): string {
        /*
         * Remove path components.
         */
        $filename = basename($filename);

        /*
         * Remove control characters and null bytes.
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

        /*
         * Keep filenames reasonably small.
         */
        if ($filename === '') {
            return 'attachment';
        }

        return mb_substr($filename, 0, 255);
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment Size
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

            if (! is_int($size)) {
                continue;
            }

            $totalBytes += $size;
        }

        $maxBytes = self::MAX_TOTAL_ATTACHMENT_SIZE_KB * 1024;

        if ($totalBytes > $maxBytes) {
            throw ValidationException::withMessages([
                'attachments' => 'The combined size of your attachments may not exceed 50 MB.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Stored Files
    |--------------------------------------------------------------------------
    */

    protected function deleteStoredFiles(array $storedFiles): void
    {
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
                 * Cleanup failure should not hide the original error.
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
    | Ticket Properties
    |--------------------------------------------------------------------------
    */

    protected function syncTicketProperties(): void
    {
        $this->subject = (string) $this->ticket->subject;
        $this->status = (string) $this->ticket->status;
        $this->priority = (string) $this->ticket->priority;
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

    public function render(
        TicketService $ticketService,
    ) {
        /*
         * Re-query the ticket rather than trusting the serialized
         * Livewire model.
         */
        $ticket = Ticket::query()
            ->with([
                'messages.attachments',
            ])
            ->find($this->ticket->id);

        if (! $ticket) {
            abort(404);
        }

        /*
         * Authorization must also happen during render because a guest
         * session can expire while the page is open.
         */
        $ticketService->requireGuestTicketAccess($ticket);

        /*
         * Keep the component's model current.
         */
        $this->ticket = $ticket;

        return view(
            'livewire.home.tickets.guest-ticket',
            [
                'ticket' => $ticket,
            ],
        );
    }
}
