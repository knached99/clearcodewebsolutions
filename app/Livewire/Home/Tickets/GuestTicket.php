<?php

namespace App\Livewire\Home\Tickets;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\WithFileUploads;

class GuestTicket extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $message = '';

    public array $attachments = [];

    public bool $showEditTicket = false;

    public string $subject = '';

    public string $status = '';

    public string $priority = '';

    protected function rules(): array
    {
        return [
            'message' => [
                'required',
                'string',
                'max:10000',
            ],
            'attachments' => [
                'nullable',
                'array',
                'max:10',
            ],
            'attachments.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:10240',
            ],
        ];
    }

    public function mount(
        Ticket $ticket,
        TicketService $ticketService,
    ): void {
        $ticketService->requireGuestTicketAccess($ticket);

        $this->ticket = $ticket->load([
            'messages.attachments',
        ]);

        $this->subject = (string) $ticket->subject;
        $this->status = (string) $ticket->status;
        $this->priority = (string) $ticket->priority;
    }

    public function sendReply(
        TicketService $ticketService,
    ): void {
        $ticket = Ticket::query()->find($this->ticket->id);

        if (! $ticket) {
            abort(404);
        }

        $ticketService->requireGuestTicketAccess($ticket);

        if ($ticket->status === 'closed') {
            return;
        }

        $this->validate();

        DB::transaction(function () use ($ticket): void {
            $message = $ticket->messages()->create([
                'author_type' => 'guest',
                'author_name' => $ticket->name,
                'message' => trim($this->message),
            ]);

            foreach ($this->attachments as $attachment) {
                $path = $attachment->store(
                    'tickets/' . $ticket->id,
                    'public',
                );

                $message->attachments()->create([
                    'path' => $path,
                    'original_name' => $attachment->getClientOriginalName(),
                    'mime_type' => $attachment->getMimeType(),
                    'size' => $attachment->getSize(),
                ]);
            }

            $ticket->update([
                'last_activity_at' => now(),
            ]);

            Notification::route(
                'mail',
                config('mail.admin_address'),
            )->notify(
                new \App\Notifications\NewTicketReplyNotification(
                    $ticket,
                    $message,
                    true,
                ),
            );
        });

        $this->ticket = $ticket
            ->fresh()
            ->load('messages.attachments');

        $this->subject = (string) $this->ticket->subject;
        $this->status = (string) $this->ticket->status;
        $this->priority = (string) $this->ticket->priority;

        $this->message = '';
        $this->attachments = [];

        session()->flash(
            'ticket-success',
            'Your reply has been sent.',
        );
    }

    public function removeAttachment(int $index): void
    {
        if (! array_key_exists($index, $this->attachments)) {
            return;
        }

        unset($this->attachments[$index]);

        $this->attachments = array_values(
            $this->attachments,
        );
    }

    public function render(
        TicketService $ticketService,
    ) {
        $ticket = Ticket::query()->find($this->ticket->id);

        if (! $ticket) {
            abort(404);
        }

        $ticketService->requireGuestTicketAccess($ticket);

        return view(
            'livewire.home.tickets.guest-ticket',
        );
    }
}