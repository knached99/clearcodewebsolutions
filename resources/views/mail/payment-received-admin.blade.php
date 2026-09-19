<x-mail::message>

# Payment received

A customer has successfully completed payment through the checkout.

<x-mail::panel>

## Payment Summary

| | |
|:--|--:|
| **Invoice** | #{{ $invoice->id }} |
| **Ticket** | #{{ $invoice->ticket?->ticket_number ?? 'N/A' }} |
| **Customer** | {{ $invoice->customer?->name ?? 'N/A' }} |
| **Email** | {{ $invoice->customer?->email ?? 'N/A' }} |
| **Amount Paid** | **${{ number_format($invoice->totalAmountInDollars(), 2) }}** |
| **Status** | **Paid ✓** |

</x-mail::panel>

## Invoice Details

@foreach ($invoice->items as $item)
### {{ $item->description ?? $item->name ?? 'Item' }}

**Amount:** ${{ number_format($item->amountInDollars(), 2) }}@if ($item->is_subscription) / month @endif

@if (!$loop->last)

---
@endif
@endforeach

@if ($invoice->isSubscription())

<x-mail::panel>

### Subscription

**Monthly recurring amount:**  
${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }} / month

**Minimum term:**  
12 months

</x-mail::panel>

@endif

## Payment completed successfully

The customer's payment has been successfully processed through Stripe.

**Invoice:** #{{ $invoice->id }}  
**Customer:** {{ $invoice->customer?->name ?? 'N/A' }}  
**Email:** {{ $invoice->customer?->email ?? 'N/A' }}

No further action is required unless follow-up is needed.

Thanks,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Automated notification • Invoice #{{ $invoice->id }}
</x-mail::subcopy>

</x-mail::message>