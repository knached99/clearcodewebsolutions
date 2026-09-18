<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceCheckoutMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Invoice $invoice)
    {
        $this->invoice->loadMissing(['customer', 'ticket', 'items']);
    }

    public function envelope(): Envelope
    {
        $ticketNumber = $this->invoice->ticket->ticket_number ?? 'Support';

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
