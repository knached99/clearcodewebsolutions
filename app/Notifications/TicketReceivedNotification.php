<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('tickets.show', [
        'ticketNumber' => $this->ticket->ticket_number,
        'token' => $this->token,
    ]);

        return (new MailMessage)
            ->subject(
                'We received your ClearCode request — ' .
                $this->ticket->ticket_number
            )
            ->greeting(
                'Hey ' . $this->ticket->name . '!'
            )
            ->line(
                'We received your message and our team will take a look shortly.'
            )
            ->line(
                'Your ticket number is ' .
                $this->ticket->ticket_number . '.'
            )
            ->action(
                'View Your Ticket',
                $url
            )
            ->line(
                'You can use this secure link to view your ticket and reply without creating an account.'
            );
    }
}