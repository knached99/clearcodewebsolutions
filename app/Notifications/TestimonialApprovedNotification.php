<?php

namespace App\Notifications;

use App\Models\Testimonials;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestimonialApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Testimonials $testimonial
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your testimonial has been approved')
            ->greeting('Thank you, ' . $this->testimonial->name . '!')
            ->line('Your testimonial has been approved and is now published on our website.')
            ->line('Here is the testimonial you submitted:')
            ->line('“' . $this->testimonial->message . '”')
            ->line(
                'Rating: ' .
                str_repeat('★', $this->testimonial->rating) .
                str_repeat('☆', 5 - $this->testimonial->rating)
            )
            ->line('We appreciate you taking the time to share your experience with us.')
            ->salutation('Thank you, Clear Code Web Solutions');
    }
}