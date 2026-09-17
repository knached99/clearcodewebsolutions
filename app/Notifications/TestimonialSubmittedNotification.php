<?php

namespace App\Notifications;

use App\Models\Testimonials;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestimonialSubmittedNotification extends Notification
{
    public function __construct(
        public Testimonials $testimonial,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(
                'Thanks for sharing your experience with Clear Code'
            )
            ->greeting(
                'Thanks, '.$this->testimonial->name.'!'
            )
            ->line(
                'We really appreciate you taking the time to share your experience with Clear Code Web Solutions.'
            )
            ->line(
                'Your testimonial has been received and is now being reviewed by our team.'
            )
            ->line(
                'Once it has been processed, we\'ll let you know when it is available to view on our website.'
            )
            ->line(
                'Thanks again for your support — it means a lot to us.'
            )
            ->salutation(
                '— The Clear Code Web Solutions Team'
            );
    }
}
