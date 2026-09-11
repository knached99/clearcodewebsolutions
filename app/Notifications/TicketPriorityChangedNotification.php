<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketPriorityChangedNotification extends Notification
{

    public function __construct(
        public Ticket $ticket,
        public string $oldPriority,
        public string $newPriority,
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
        \Log::info('PRIORITY NOTIFICATION TOKEN', [
            'ticket' => $this->ticket->ticket_number,
            'token' => $token,
            'token_length' => strlen($token),
        ]);

        $url = $ticketService->createGuestTicketUrl(
            ticket: $this->ticket,
            token: $token,
        );

        \Log::info('PRIORITY NOTIFICATION URL', [
            'url' => $url,
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
