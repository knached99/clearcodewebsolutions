<x-mail::message>

# Payment Request

Hi {{ $invoice->customer->name }},

A payment is due for the work completed on **Ticket #{{ $invoice->ticket->ticket_number ?? 'N/A' }}**.

<x-mail::panel>

### Payment Summary

**Amount Due Today:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}

@if ($invoice->isSubscription())
**Monthly Recurring:** ${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }}/month

**Subscription Term:** 12-month minimum
@endif

</x-mail::panel>

### Invoice Details

@foreach ($invoice->items as $item)
- **{{ $item->description }}** — ${{ number_format($item->amountInDollars(), 2) }}@if ($item->is_subscription)/month@endif
@endforeach

<x-mail::panel>

**Due Today:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}

@if ($invoice->isSubscription())
**Recurring Monthly:** ${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }}
@endif

</x-mail::panel>

When you're ready, click the button below to securely complete your payment.

<x-mail::button :url="route('checkout.show', $invoice->checkout_token)" color="primary">
Complete Secure Payment
</x-mail::button>

If you have any questions about this invoice, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}

</x-mail::message>