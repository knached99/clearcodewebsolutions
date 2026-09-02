<?php

namespace App\Livewire\Teams\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $status = '';

    public string $newMessage = '';

    public array $attachments = [];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;

        $this->status = $ticket->status;

        $this->loadTicket();
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function updatedStatus(string $value): void
    {
        $this->validate([
            'status' => [
                'required',
                'in:open,pending,resolved,closed',
            ],
        ]);

        if ($this->status === $this->ticket->status) {
            return;
        }

        $this->ticket->update([
            'status' => $this->status,
            'last_activity_at' => now(),
        ]);

        $this->ticket->refresh();

        $this->dispatch('ticket-status-updated');
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
                    'tickets/' . $this->ticket->id,
                    'public'
                );

                $message->attachments()->create([
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $attachment->getMimeType(),
                    'file_size' => $attachment->getSize(),
                ]);
            }

            $this->ticket->update([
                'last_activity_at' => now(),
            ]);
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
    | Reload ticket
    |--------------------------------------------------------------------------
    */

    protected function loadTicket(): void
    {
        $this->ticket->load([
            'messages.attachments',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view('livewire.teams.tickets.show');
    }
}