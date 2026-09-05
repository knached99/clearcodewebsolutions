<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public string $newStatus,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(
                "Ticket #{$this->ticket->ticket_number} status updated"
            )
            ->greeting('Ticket status updated')
            ->line(
                "Ticket #{$this->ticket->ticket_number} has changed " .
                "from {$this->oldStatus} to {$this->newStatus}."
            )
            ->line(
                "Subject: {$this->ticket->subject}"
            )
            ->action(
                'View Ticket',
                route('team.tickets.show', [
                    'current_team' => $this->ticket->team->slug,
                    'ticket' => $this->ticket->ticket_number,
                ])
            );
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}