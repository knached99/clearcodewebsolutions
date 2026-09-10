<?php

namespace App\Notifications;

use App\Models\Team;
use App\Models\Testimonials;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTestimonialSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Testimonials $testimonial,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $adminName = $notifiable->name ?? 'there';

        $team = Team::query()
            ->where('id', 1)
            ->first();

        $testimonialUrl = $team
            ? route('testimonials.show', [
                'current_team' => $team->slug,
                'testimonial' => $this->testimonial->id,
            ])
            : url('/');

        return (new MailMessage)
            ->subject(
                'New testimonial submitted — '.
                $this->testimonial->name
            )
            ->greeting(
                'Hello '.$adminName.','
            )
            ->line(
                $this->testimonial->name.
                ' has submitted a new testimonial.'
            )
            ->line(
                'Email: '.
                ($this->testimonial->email ?: 'Not provided')
            )
            ->line(
                'Company: '.
                ($this->testimonial->company ?: 'Not provided')
            )
            ->line(
                'Role: '.
                ($this->testimonial->role ?: 'Not provided')
            )
            ->line(
                'Rating: '.
                str_repeat('★', $this->testimonial->rating).
                str_repeat('☆', 5 - $this->testimonial->rating)
            )
            ->line('Message:')
            ->line($this->testimonial->message)
            ->line(
                'This testimonial is currently pending moderation.'
            )
            ->action(
                'View Testimonial',
                $testimonialUrl
            )
            ->salutation(
                'Clear Code Web Solutions'
            );
    }
}
