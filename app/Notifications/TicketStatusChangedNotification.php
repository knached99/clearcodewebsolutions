<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Services\TicketService;
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
         * is actually being processed.
         *
         * This prevents team route defaults from leaking into the
         * customer-facing email URL.
         */
        $ticketUrl = app(TicketService::class)
            ->createGuestTicketUrl(
                $this->ticket,
                60
            );

        return (new MailMessage)
            ->subject('Ticket status updated')
            ->line(
                'Ticket #' .
                $this->ticket->ticket_number .
                ' has changed from ' .
                $this->oldStatus .
                ' to ' .
                $this->newStatus .
                '.'
            )
            ->line(
                'Subject: ' .
                $this->ticket->subject
            )
            ->action(
                'View Ticket',
                $ticketUrl
            )
            ->line(
                'Regards,'
            );
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}