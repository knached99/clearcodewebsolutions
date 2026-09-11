<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChangedNotification extends Notification
{

    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public string $newStatus,
        public ?string $ticketUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticketService = app(TicketService::class);

        $token = $ticketService->createGuestAccessToken(
            ticket: $this->ticket,
            minutes: 60 * 24 * 30,
        );

        $url = $ticketService->createGuestTicketUrl(
            ticket: $this->ticket,
            token: $token,
        );

        return (new MailMessage)
            ->subject('Ticket status updated')
            ->greeting('Hello,')
            ->line(
                'Ticket #'.
                $this->ticket->ticket_number.
                ' has changed from '.
                $this->oldStatus.
                ' to '.
                $this->newStatus.
                '.'
            )
            ->line(
                'Subject: '.$this->ticket->subject
            )
            ->action(
                'View Ticket',
                $url
            )
            ->line(
                'This ticket link will expire in 30 days.'
            );
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
