<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ticket->loadMissing('team');

        return (new MailMessage)
            ->subject(
                'New Ticket '.$this->ticket->ticket_number
            )
            ->greeting('New support ticket')
            ->line(
                $this->ticket->name.
                ' submitted a new support request.'
            )
            ->line(
                'Email: '.$this->ticket->email
            )
            ->action(
                'Open Ticket',
                route('team.tickets.show', [
                    'current_team' => $this->ticket->team->slug,
                    'ticket' => $this->ticket->ticket_number,
                ])
            )
            ->line(
                'Ticket: '.$this->ticket->ticket_number
            );
    }
}
