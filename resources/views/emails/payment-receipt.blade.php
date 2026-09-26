<x-mail::message>

# Payment Received

Hi {{ $invoice->customer->name }},

Thank you for your payment! Your payment for **Ticket #{{ $invoice->ticket->ticket_number ?? 'N/A' }}** has been successfully processed.

<x-mail::panel>

### Payment Summary

**Amount Paid:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}

@if ($invoice->isSubscription())
**Monthly Recurring:** ${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }}/month x{{ $invoice->subscription_months ?? 12 }}
**Subscription Term:** {{ $invoice->subscription_months ?? 12 }} months minimum
@endif

**Ticket:** #{{ $invoice->ticket->ticket_number ?? 'N/A' }}

**Status:** Paid ✓

</x-mail::panel>

A PDF copy of your receipt is attached to this email for your records.

We appreciate your business and thank you for choosing {{ config('app.name') }}.

Thanks,<br>
{{ config('app.name') }}

</x-mail::message>