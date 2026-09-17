<x-mail::message>
# Payment Received

Hi {{ $invoice->customer->name }},

Thank you for your payment! Your transaction for **Ticket #{{ $invoice->ticket->ticket_number ?? 'N/A' }}** was successful.

**Amount Paid:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}

A PDF copy of your receipt is attached to this email for your records.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
