<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {
        $this->invoice->loadMissing([
            'customer',
            'items',
            'ticket',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Received - Invoice #' . $this->invoice->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payment-received',
        );
    }
}