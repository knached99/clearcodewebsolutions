<x-mail::message>
# Secure Checkout Request

Hi {{ $invoice->customer->name }},

A payment has been requested for the work done on **Ticket #{{ $invoice->ticket->ticket_number ?? 'N/A' }}**.

@if($invoice->isSubscription())
**Amount Due Today:** ${{ number_format($invoice->totalAmountInDollars(), 2) }} (Then billed monthly)
@else
**Total Amount:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}
@endif

<x-mail::panel>
### Invoice Summary
@foreach($invoice->items as $item)
- **{{ $item->description }}**: ${{ number_format($item->amountInDollars(), 2) }} {{ $item->is_subscription ? '/ month' : '' }}
@endforeach
</x-mail::panel>

Please click the button below to complete your payment via our secure checkout.

<x-mail::button :url="route('checkout.show', $invoice->checkout_token)" color="primary">
Complete Payment
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
