<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public \App\Models\Ticket $ticket,
        public \App\Models\TicketMessage $ticketMessage,
        public bool $isAdmin
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->isAdmin 
            ? route('team.tickets.show', ['current_team' => $this->ticket->team->slug, 'ticket' => $this->ticket->id]) 
            : url('/tickets'); // The user needs to verify their email to access their tickets.

        $author = $this->ticketMessage->author_name;

        return (new MailMessage)
            ->subject('New Reply on Ticket: ' . $this->ticket->ticket_number)
            ->greeting('Hello,')
            ->line("{$author} has replied to ticket {$this->ticket->ticket_number}:")
            ->line('"' . \Illuminate\Support\Str::limit($this->ticketMessage->message, 100) . '"')
            ->action('View Ticket', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
