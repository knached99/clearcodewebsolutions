<x-mail::message>

# Payment Received

Hi {{ $invoice->customer->name }},

Thank you for your payment! Your payment for **Ticket #{{ $invoice->ticket->ticket_number ?? 'N/A' }}** has been successfully processed.

<x-mail::panel>

### Payment Summary

**Amount Paid:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}

**Ticket:** #{{ $invoice->ticket->ticket_number ?? 'N/A' }}

**Status:** Paid ✓

</x-mail::panel>

A PDF copy of your receipt is attached to this email for your records.

We appreciate your business and thank you for choosing {{ config('app.name') }}.

Thanks,<br>
{{ config('app.name') }}

</x-mail::message>