<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceCheckoutMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $checkoutUrl,
        public Ticket $ticket,
        public float $baseAmount,
        public string $description,
        public bool $isSubscription,
        public array $selectedAddons,
        public int $totalAmountInCents,
        public int $monthlyRecurringAmountInCents,
        public ?int $subscriptionMonths = null,
    ) {}

    public function envelope(): Envelope
    {
        $ticketNumber = $this->ticket->ticket_number ?? 'Support';

        return new Envelope(
            subject: "Payment Requested: Ticket #{$ticketNumber}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice-checkout',
        );
    }
}