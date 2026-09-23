<x-mail::message>

# Payment Request

Hi {{ $ticket->customer?->name ?? 'there' }},

A payment is due for the work completed on **Ticket #{{ $ticket->ticket_number ?? 'N/A' }}**.

<x-mail::panel>

### Payment Summary

**Amount Due Today:**  
${{ number_format($totalAmountInCents / 100, 2) }}

@if ($monthlyRecurringAmountInCents > 0)
**Monthly Recurring:**  
${{ number_format($monthlyRecurringAmountInCents / 100, 2) }}/month

@if ($isSubscription)
**Subscription Term:**  
12-month minimum
@endif
@endif

</x-mail::panel>

### Invoice Details

**{{ $description }}**  
${{ number_format($baseAmount, 2) }}  
@if ($isSubscription)
<span style="font-size: 12px; color: #6b7280;">Recurring Monthly</span>
@else
<span style="font-size: 12px; color: #6b7280;">One-time charge</span>
@endif

@if (!empty($selectedAddons))
@foreach ($selectedAddons as $addon)
---
**{{ $addon['name'] }}**  
${{ number_format($addon['price'], 2) }}  
<span style="font-size: 12px; color: #6b7280;">Recurring monthly add-on</span>
@endforeach
@endif

<x-mail::panel>

**Due Today:**  
${{ number_format($totalAmountInCents / 100, 2) }}

@if ($monthlyRecurringAmountInCents > 0)
**Recurring Monthly:**  
${{ number_format($monthlyRecurringAmountInCents / 100, 2) }}
@endif

</x-mail::panel>

When you're ready, click the button below to securely complete your payment.

<x-mail::button :url="$checkoutUrl" color="primary">
Complete Secure Payment
</x-mail::button>

If you have any questions about this invoice, please don't hesitate to contact us.

Thanks,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
This is an automated payment request for Ticket #{{ $ticket->ticket_number ?? 'N/A' }}.
</x-mail::subcopy>

</x-mail::message>