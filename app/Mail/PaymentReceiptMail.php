<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
        $this->invoice->loadMissing(['customer', 'ticket', 'items']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Receipt: Invoice #'.strtoupper(substr($this->invoice->id, 0, 8)),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-receipt',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->invoice->generatePdf(),
                'Invoice_'.strtoupper(substr($this->invoice->id, 0, 8)).'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
