<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalDecisionMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $renewalDate,
        public int $monthlyRecurringAmountInCents,
        public int $addonCount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your 12-month renewal decision is due',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-renewal-decision',
        );
    }
}
