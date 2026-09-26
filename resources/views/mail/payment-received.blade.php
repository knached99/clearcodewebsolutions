<x-mail::message>

# Payment received 🎉

Hi {{ $invoice->customer?->name ?? 'there' }},

Thank you for your payment. Your transaction has been successfully completed and your invoice is now marked as **Paid**.

<x-mail::panel>

## Payment Summary

| | |
|:--|--:|
| **Invoice** | #{{ $invoice->id }} |
| **Ticket** | #{{ $invoice->ticket?->ticket_number ?? 'N/A' }} |
| **Amount Paid** | **${{ number_format($invoice->totalAmountInDollars(), 2) }}** |
| **Payment Status** | **Paid ✓** |

</x-mail::panel>

## What you purchased

@foreach ($invoice->items as $item)
**{{ $item->description ?? $item->name ?? 'Item' }}**  
${{ number_format($item->amountInDollars(), 2) }}@if ($item->is_subscription) / month x{{ $invoice->subscription_months ?? 12 }} @endif

@if (!$loop->last)

---
@endif
@endforeach

@if ($invoice->isSubscription())

<x-mail::panel>

### Recurring Subscription

**Monthly payment:** ${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }} / month x{{ $invoice->subscription_months ?? 12 }}

**Next payment:** {{ $invoice->nextBillingDate()->format('F j, Y') }}

**Renews on:** {{ $invoice->renewalDate()->format('F j, Y') }}

**Minimum term:** {{ $invoice->subscription_months ?? 12 }} months

Your recurring subscription will continue according to the terms of your agreement.

</x-mail::panel>

@endif

### Your receipt

Please keep this email for your records. Your payment details are associated with **Invoice #{{ $invoice->id }}**.

If you have any questions about your payment or invoice, please contact our team and reference your invoice number.

Thanks for choosing **{{ config('app.name') }}**.

Warm regards,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
This is an automated payment confirmation for Invoice #{{ $invoice->id }}.
</x-mail::subcopy>

</x-mail::message>