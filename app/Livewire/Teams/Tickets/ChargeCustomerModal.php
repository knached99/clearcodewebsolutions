<?php

namespace App\Livewire\Teams\Tickets;

use App\Enums\PaymentType;
use App\Mail\InvoiceCheckoutMail;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Services\StripeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ChargeCustomerModal extends Component
{
    public bool $show = false;

    public ?Ticket $ticket = null;

    public string $amount = '';

    public string $description = 'Development Services';

    public bool $isSubscription = false;

    public array $selectedAddonPriceIds = [];

    #[Computed]
    public function existingInvoice(): ?Invoice
    {
        if (! $this->ticket) {
            return null;
        }

        return $this->ticket->latestInvoice;
    }

    #[Computed]
    public function availableAddons(): array
    {
        return Cache::remember('stripe_active_addons', 3600, function () {
            try {
                return app(StripeService::class)->getActiveAddons();
            } catch (\Exception $e) {
                Log::error('Failed to fetch Stripe add-ons: '.$e->getMessage());

                return [];
            }
        });
    }

    #[Computed]
    public function baseAmountInCents(): int
    {
        if (empty($this->amount)) {
            return 0;
        }

        return (int) round((float) $this->amount * 100);
    }

    #[Computed]
    public function addonsMonthlyAmountInCents(): int
    {
        $total = 0;
        $addons = $this->availableAddons();

        foreach ($this->selectedAddonPriceIds as $priceId) {
            if (isset($addons[$priceId])) {
                $total += $addons[$priceId]['price_in_cents'];
            }
        }

        return $total;
    }

    #[Computed]
    public function totalAmountInCents(): int
    {
        return $this->baseAmountInCents() + $this->addonsMonthlyAmountInCents();
    }

    public function hasRecurringInvoiceItems(): bool
    {
        if ($this->isSubscription) {
            return true;
        }

        return ! empty($this->selectedAddonPriceIds);
    }

    public function resolveInvoicePaymentType(): PaymentType
    {
        return $this->hasRecurringInvoiceItems()
            ? PaymentType::Subscription
            : PaymentType::OneTime;
    }

    #[Computed]
    public function monthlyRecurringAmountInCents(): int
    {
        $monthlyBase = $this->isSubscription ? $this->baseAmountInCents() : 0;

        return $monthlyBase + $this->addonsMonthlyAmountInCents();
    }

    #[On('open-charge-modal')]
    public function openModal(int $ticketId)
    {
        $this->ticket = Ticket::findOrFail($ticketId);
        $this->authorize('update', $this->ticket);

        $this->resetForm();
        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
        $this->resetErrorBag();
    }

    public function resetForm()
    {
        $this->amount = '';
        $this->description = 'Development Services - Ticket #'.($this->ticket?->ticket_number ?? 'N/A');
        $this->isSubscription = false;
        $this->selectedAddonPriceIds = [];
        $this->resetErrorBag();
    }

    public function submit(StripeService $stripe)
    {
        $this->authorize('update', $this->ticket);

        $this->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|min:1|max:255',
        ]);

        try {
            $customer = $this->ticket->resolveCustomer();

            if (! $customer) {
                $this->addError('submit', 'Unable to resolve customer for this ticket.');

                return;
            }

            $invoicePaymentType = $this->resolveInvoicePaymentType();
            $invoice = new Invoice();
            $invoice->ticket_id = $this->ticket->id;
            $invoice->customer_id = $customer->id ?? null;
            $invoice->checkout_token = \Illuminate\Support\Str::random(32);
            $invoice->total_amount = $this->totalAmountInCents;
            $invoice->payment_type = $invoicePaymentType;
            $invoice->subscription_months = $invoicePaymentType === PaymentType::Subscription ? 12 : null;
            $invoice->save();

            $invoice->items()->create([
                'description' => $this->description.
                    ($invoicePaymentType === PaymentType::Subscription ? ' (Monthly - 12 Mo. Minimum)' : ''),
                'amount' => $this->baseAmountInCents,
                'is_subscription' => $invoicePaymentType === PaymentType::Subscription,
                'stripe_price_id' => null,
            ]);

            $addons = $this->availableAddons();
            $selectedAddonDetails = [];

            foreach ($this->selectedAddonPriceIds as $priceId) {
                if (! isset($addons[$priceId])) {
                    continue;
                }

                $selectedAddonDetails[] = $addons[$priceId];

                $invoice->items()->create([
                    'description' => $addons[$priceId]['name'],
                    'amount' => $addons[$priceId]['price_in_cents'],
                    'is_subscription' => $invoicePaymentType === PaymentType::Subscription,
                    'stripe_price_id' => $invoicePaymentType === PaymentType::Subscription ? $priceId : null,
                ]);
            }

            $customCheckoutUrl = route('checkout.show', ['token' => $invoice->checkout_token]);

            Mail::to($customer->email)->send(new InvoiceCheckoutMail(
                checkoutUrl: $customCheckoutUrl,
                ticket: $this->ticket,
                baseAmount: (float) $this->amount,
                description: $this->description,
                isSubscription: $this->isSubscription,
                selectedAddons: $selectedAddonDetails,
                totalAmountInCents: $this->totalAmountInCents,
                monthlyRecurringAmountInCents: $this->monthlyRecurringAmountInCents,
                subscriptionMonths: $this->isSubscription ? 12 : null,
            ));

            session()->flash('success', 'Checkout link generated and emailed successfully.');

            return redirect()->route('invoices.show', $invoice);
        } catch (\Exception $e) {
            Log::error('Invoice generation error: '.$e->getMessage());
            $this->addError('submit', 'An error occurred: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.teams.tickets.charge-customer-modal', [
            'availableAddons' => $this->availableAddons(),
        ]);
    }
}
