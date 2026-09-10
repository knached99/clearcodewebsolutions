<?php

namespace App\Livewire\Teams\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessageAttachment;
use App\Notifications\NewTicketReplyNotification;
use App\Notifications\TicketPriorityChangedNotification;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Show extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $status = '';

    public string $priority = '';

    public string $newMessage = '';

    public array $attachments = [];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;

        $this->status = (string) $ticket->status;

        $this->priority = (string) $ticket->priority;

        $this->loadTicket();
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(string $value): void
    {
        $value = strtolower(trim($value));

        validator(
            ['status' => $value],
            [
                'status' => [
                    'required',
                    'in:open,pending,resolved,closed',
                ],
            ]
        )->validate();

        if ($value === $this->ticket->status) {
            $this->status = $value;

            return;
        }

        $oldStatus = (string) $this->ticket->status;

        $this->ticket->update([
            'status' => $value,
            'last_activity_at' => now(),
        ]);

        $this->ticket->refresh();

        $this->status = (string) $this->ticket->status;
        $this->priority = (string) $this->ticket->priority;

        Notification::route(
            'mail',
            $this->ticket->email
        )->notify(
            new TicketStatusChangedNotification(
                $this->ticket,
                $oldStatus,
                $this->ticket->status
            )
        );

        $this->dispatch('ticket-status-updated');
    }
    /*
    |--------------------------------------------------------------------------
    | Priority
    |--------------------------------------------------------------------------
    */

    public function updatePriority(string $value): void
    {
        $value = strtolower(trim($value));

        validator(
            ['priority' => $value],
            [
                'priority' => [
                    'required',
                    'in:low,normal,high,urgent',
                ],
            ]
        )->validate();

        if ($value === $this->ticket->priority) {
            $this->priority = $value;

            return;
        }

        $oldPriority = (string) $this->ticket->priority;

        $this->ticket->update([
            'priority' => $value,
            'last_activity_at' => now(),
        ]);

        $this->ticket->refresh();

        $this->priority = (string) $this->ticket->priority;
        $this->status = (string) $this->ticket->status;

        Notification::route(
            'mail',
            $this->ticket->email
        )->notify(
            new TicketPriorityChangedNotification(
                $this->ticket,
                $oldPriority,
                $this->ticket->priority
            )
        );

        $this->dispatch('ticket-priority-updated');
    }

    /*
    |--------------------------------------------------------------------------
    | Send Message
    |--------------------------------------------------------------------------
    */

    public function sendMessage(): void
    {
        $this->validate([
            'newMessage' => [
                'required',
                'string',
                'max:10000',
            ],
            'attachments.*' => [
                'file',
                'max:10240',
            ],
        ]);

        DB::transaction(function () {
            $message = $this->ticket->messages()->create([
                'author_type' => 'support',
                'author_name' => auth()->user()->name,
                'message' => trim($this->newMessage),
            ]);

            foreach ($this->attachments as $attachment) {
                $path = $attachment->store(
                    'tickets/'.$this->ticket->id,
                    'public'
                );

                $message->attachments()->create([
                    'original_name' => $attachment->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $attachment->getMimeType(),
                    'size' => $attachment->getSize(),
                ]);
            }

            $this->ticket->update([
                'last_activity_at' => now(),
            ]);

            Notification::route(
                'mail',
                $this->ticket->email
            )->notify(
                new NewTicketReplyNotification(
                    $this->ticket,
                    $message,
                    false
                )
            );
        });

        $this->reset([
            'newMessage',
            'attachments',
        ]);

        $this->loadTicket();

        $this->dispatch('message-sent');
    }

    /*
    |--------------------------------------------------------------------------
    | Upload validation
    |--------------------------------------------------------------------------
    */

    public function updatedAttachments(): void
    {
        $this->validate([
            'attachments.*' => [
                'file',
                'max:10240',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Remove attachment
    |--------------------------------------------------------------------------
    */

    public function removeAttachment(int $index): void
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);

            $this->attachments = array_values(
                $this->attachments
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment Preview
    |--------------------------------------------------------------------------
    */

    public function preview(
        TicketMessageAttachment $attachment
    ): StreamedResponse {
        $this->authorizeAttachment($attachment);

        $disk = Storage::disk('public');

        abort_unless(
            $disk->exists($attachment->path),
            404,
            'Attachment file not found.'
        );

        $mime = $attachment->mime_type
            ?: $disk->mimeType($attachment->path)
            ?: 'application/octet-stream';

        return response()->stream(
            function () use ($disk, $attachment) {
                $stream = $disk->readStream($attachment->path);

                abort_unless(
                    $stream !== false,
                    404,
                    'Unable to read attachment.'
                );

                fpassthru($stream);

                fclose($stream);
            },
            200,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.
                    addslashes($attachment->original_name).
                    '"',
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment Download
    |--------------------------------------------------------------------------
    */

    public function download(
        TicketMessageAttachment $attachment
    ): StreamedResponse {
        $this->authorizeAttachment($attachment);

        $disk = Storage::disk('public');

        abort_unless(
            $disk->exists($attachment->path),
            404,
            'Attachment file not found.'
        );

        return $disk->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attachment authorization
    |--------------------------------------------------------------------------
    */

    protected function authorizeAttachment(
        TicketMessageAttachment $attachment
    ): void {
        $attachment->loadMissing('message');

        abort_unless(
            $attachment->message &&
            (int) $attachment->message->ticket_id ===
                (int) $this->ticket->id,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reload
    |--------------------------------------------------------------------------
    */

    protected function loadTicket(): void
    {
        $this->ticket->load([
            'messages.attachments',
        ]);
    }

    public function render()
    {
        return view('livewire.teams.tickets.show');
    }
}
