<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentLinkNotification extends Notification
{
    use Queueable;

    public string $checkoutUrl;

    public function __construct(string $checkoutUrl)
    {
        $this->checkoutUrl = $checkoutUrl;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Complete Your Secure Payment')
            // Pass the URL to the custom Blade view we created previously
            ->view('emails.payment-link', ['checkoutUrl' => $this->checkoutUrl]);
    }
}
