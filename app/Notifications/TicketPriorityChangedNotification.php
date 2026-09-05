<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketPriorityChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public string $oldPriority,
        public string $newPriority,
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
        $url = route('team.tickets.show', [
            'current_team' => $this->ticket->team->slug,
            'ticket' => $this->ticket->ticket_number,
        ]);

        return (new MailMessage)
            ->subject(
                "Ticket Priority Changed: {$this->ticket->ticket_number}"
            )
            ->greeting('Hello,')
            ->line(
                "The priority of ticket {$this->ticket->ticket_number} has changed."
            )
            ->line(
                "Priority changed from {$this->oldPriority} to {$this->newPriority}."
            )
            ->line(
                "Subject: {$this->ticket->subject}"
            )
            ->action(
                'View Ticket',
                $url
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