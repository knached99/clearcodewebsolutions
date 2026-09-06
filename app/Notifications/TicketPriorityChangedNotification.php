<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketPriorityChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $oldPriority,
        public string $newPriority,
        public ?string $ticketUrl = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /*
         * ALWAYS generate the guest URL when the queued notification
         * is actually processed.
         */
        $ticketUrl = app(TicketService::class)
            ->createGuestTicketUrl(
                $this->ticket,
                60
            );

        return (new MailMessage)
            ->subject(
                "Ticket Priority Changed: {$this->ticket->ticket_number}"
            )
            ->greeting('Hello,')
            ->line(
                "The priority of ticket " .
                "{$this->ticket->ticket_number} has changed."
            )
            ->line(
                "Priority changed from {$this->oldPriority} " .
                "to {$this->newPriority}."
            )
            ->line(
                "Subject: {$this->ticket->subject}"
            )
            ->action(
                'View Ticket',
                $ticketUrl
            )
            ->line(
                'This ticket link will expire in 60 minutes.'
            );
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}