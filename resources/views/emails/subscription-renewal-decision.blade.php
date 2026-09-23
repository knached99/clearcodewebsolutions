<x-mail::message>

# Your 12-month renewal decision is due

Hi {{ $invoice->customer?->name ?? 'there' }},

Your current 12-month subscription minimum is coming to an end on **{{ $renewalDate }}**.

We need your decision before that date:

- If you want to continue, we will renew your service for another 12-month term.
- If you do not want the recurring add-ons anymore, those services will cease when the renewal is processed.
- If the base service is not fully paid off and you decline the renewal, the website/app will remain offline until the balance is paid.
- If the base amount is fully paid and you choose to continue, the website stays live and the service remains active.

<x-mail::panel>

### Current recurring totals

**Monthly recurring total:** ${{ number_format($monthlyRecurringAmountInCents / 100, 2) }}

@if ($addonCount > 0)
**Recurring add-ons active:** {{ $addonCount }}
@endif

</x-mail::panel>

Please review your renewal options before the deadline. If you choose to continue, no further action is needed and your service will stay active.

<x-mail::button :url="route('checkout.show', ['token' => $invoice->checkout_token])" color="primary">
Review renewal options
</x-mail::button>

If you prefer to stop the recurring service, please reply to this email and let us know. When the renewal is declined and the base amount is still outstanding, the site/app will not be live until the balance is settled.

Thanks,<br>
**{{ config('app.name') }}**

</x-mail::message>
