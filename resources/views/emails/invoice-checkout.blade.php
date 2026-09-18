<x-mail::message>
    # Secure Checkout Request

    Hi {{ $invoice->customer->name }},

    A payment has been requested for the work done on **Ticket #{{ $invoice->ticket->ticket_number ?? 'N/A' }}**.

    @if ($invoice->isSubscription())
        **Amount Due Today:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}

        **Then:** ${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }}/month

        **Subscription Term:** 12 month minimum
    @else
        **Amount Due Today:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}

        **Then:** ${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }}/month
    @endif

    <x-mail::panel>
        ### Invoice Summary

        @foreach ($invoice->items as $item)
            * **{{ $item->description }}**: ${{ number_format($item->amountInDollars(), 2) }}@if ($item->is_subscription)
                / month
            @endif
        @endforeach

        ---

        **Due Today:** ${{ number_format($invoice->totalAmountInDollars(), 2) }}

        **Monthly Recurring:** ${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }}
    </x-mail::panel>

    Please click the button below to complete your payment via our secure checkout.

    <x-mail::button :url="route('checkout.show', $invoice->checkout_token)" color="primary">
        Complete Payment
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
