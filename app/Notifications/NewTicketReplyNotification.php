<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Services\TicketService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewTicketReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketMessage $ticketMessage,
        public bool $isAdmin,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->isAdmin) {
            /*
             * CUSTOMER -> ADMIN
             *
             * Admin ticket URL:
             *
             * /{team-slug}/tickets/{ticket-number}?message={message-id}
             *
             * Example:
             * /khaled-nacheds-team/tickets/TCKT-TOTPXSWD?message=15
             */
            $url = route('team.tickets.show', [
                'current_team' => $this->ticket->team->slug,
                'ticket' => $this->ticket->ticket_number,
                'message' => $this->ticketMessage->id,
            ]);
        } else {
            /*
             * ADMIN -> CUSTOMER
             *
             * Generate a separate access token so we don't invalidate
             * previously issued customer ticket URLs.
             */
            $ticketService = app(TicketService::class);

            $token = $ticketService->createGuestAccessToken(
                ticket: $this->ticket,
                minutes: 60 * 24 * 30,
            );

            $url = $ticketService->createGuestTicketUrl(
                ticket: $this->ticket,
                token: $token,
            );

            /*
             * Deep-link directly to the message that generated
             * this notification.
             */
            $url .= '?message='.$this->ticketMessage->id;
        }

        $author = $this->ticketMessage->author_name;

        return (new MailMessage)
            ->subject(
                'New Reply on Ticket: '.
                $this->ticket->ticket_number
            )
            ->greeting('Hello,')
            ->line(
                "{$author} has replied to ticket {$this->ticket->ticket_number}:"
            )
            ->line(
                '"'.
                Str::limit(
                    $this->ticketMessage->message,
                    100
                ).
                '"'
            )
            ->action(
                'View Ticket',
                $url
            );
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
