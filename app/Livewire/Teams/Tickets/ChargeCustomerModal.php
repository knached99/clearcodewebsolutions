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

    /**
     * Controls ONLY the base charge.
     *
     * false = base charge is one-time
     * true  = base charge is monthly recurring with 12-month minimum
     */
    public bool $isSubscription = false;

    /**
     * Selected Stripe Price IDs for monthly addons.
     *
     * Addons are ALWAYS recurring monthly.
     */
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
                Log::error(
                    'Failed to fetch Stripe add-ons: '.$e->getMessage()
                );

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

    /**
     * Amount due today.
     *
     * This includes:
     * - the base charge
     * - the first month's addon charges
     */
    #[Computed]
    public function totalAmountInCents(): int
    {
        return $this->baseAmountInCents()
            + $this->addonsMonthlyAmountInCents();
    }

    /**
     * Determines whether the invoice contains ANY recurring items.
     *
     * An invoice is considered a subscription when:
     * - the base charge is recurring OR
     * - at least one monthly addon is selected
     */
    public function hasRecurringInvoiceItems(): bool
    {
        return $this->isSubscription
            || ! empty($this->selectedAddonPriceIds);
    }

    /**
     * Determines the overall invoice payment type.
     *
     * IMPORTANT:
     * This describes the invoice as a whole.
     * It does NOT determine whether the base item itself is recurring.
     */
    public function resolveInvoicePaymentType(): PaymentType
    {
        return $this->hasRecurringInvoiceItems()
            ? PaymentType::Subscription
            : PaymentType::OneTime;
    }

    /**
     * Total amount that will recur every month.
     *
     * Base charge is recurring only when the base subscription checkbox
     * is enabled.
     *
     * Addons are always monthly recurring.
     */
    #[Computed]
    public function monthlyRecurringAmountInCents(): int
    {
        $monthlyBase = $this->isSubscription
            ? $this->baseAmountInCents()
            : 0;

        return $monthlyBase
            + $this->addonsMonthlyAmountInCents();
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

        $this->description = 'Development Services - Ticket #'.
            ($this->ticket?->ticket_number ?? 'N/A');

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
                $this->addError(
                    'submit',
                    'Unable to resolve customer for this ticket.'
                );

                return;
            }

            /*
             * The invoice can be a subscription even when the base
             * charge itself is one-time.
             *
             * Example:
             *
             * Base:  $20,000 one-time
             * Addon: $500/month
             *
             * Invoice payment type = Subscription
             * Base item is_subscription = false
             * Addon item is_subscription = true
             */
            $invoicePaymentType = $this->resolveInvoicePaymentType();

            $invoice = new Invoice();

            $invoice->ticket_id = $this->ticket->id;
            $invoice->customer_id = $customer->id ?? null;
            $invoice->checkout_token = \Illuminate\Support\Str::random(32);

            /*
             * Amount due today:
             *
             * base amount
             * +
             * first month's recurring addon charges
             */
            $invoice->total_amount = $this->totalAmountInCents;

            /*
             * Overall invoice type.
             *
             * This is Subscription if ANY recurring item exists.
             */
            $invoice->payment_type = $invoicePaymentType;

            /*
             * A recurring invoice has a 12-month minimum.
             * One-time invoices do not.
             */
            $invoice->subscription_months =
                $invoicePaymentType === PaymentType::Subscription
                    ? 12
                    : null;

            $invoice->save();

            /*
             * ============================================================
             * BASE CHARGE
             * ============================================================
             *
             * IMPORTANT:
             * Use $this->isSubscription here.
             *
             * DO NOT use:
             *
             * $invoicePaymentType === PaymentType::Subscription
             *
             * because the invoice could be recurring solely because
             * of monthly addons.
             */
            $invoice->items()->create([
                'description' => $this->description.
                    ($this->isSubscription
                        ? ' (Monthly - 12 Mo. Minimum)'
                        : ' (One-Time)'),

                'amount' => $this->baseAmountInCents,

                /*
                 * Base charge's own billing type.
                 */
                'is_subscription' => $this->isSubscription,

                /*
                 * Base custom amount does not use a Stripe recurring price.
                 */
                'stripe_price_id' => null,
            ]);

            /*
             * ============================================================
             * MONTHLY ADDONS
             * ============================================================
             *
             * Every selected addon is recurring monthly.
             */
            $addons = $this->availableAddons();

            $selectedAddonDetails = [];

            foreach ($this->selectedAddonPriceIds as $priceId) {
                if (! isset($addons[$priceId])) {
                    continue;
                }

                $addon = $addons[$priceId];

                $selectedAddonDetails[] = $addon;

                $invoice->items()->create([
                    'description' => $addon['name'],
                    'amount' => $addon['price_in_cents'],

                    /*
                     * Addons are ALWAYS recurring.
                     */
                    'is_subscription' => true,

                    /*
                     * Addons use their Stripe recurring Price ID.
                     */
                    'stripe_price_id' => $priceId,
                ]);
            }

            $customCheckoutUrl = route(
                'checkout.show',
                ['token' => $invoice->checkout_token]
            );

            /*
             * Send checkout email.
             *
             * isSubscription describes the BASE charge specifically.
             * monthlyRecurringAmountInCents contains the recurring
             * base amount (when applicable) + recurring addons.
             */
            Mail::to($customer->email)->send(
                new InvoiceCheckoutMail(
                    checkoutUrl: $customCheckoutUrl,
                    ticket: $this->ticket,
                    baseAmount: (float) $this->amount,
                    description: $this->description,
                    isSubscription: $this->isSubscription,
                    selectedAddons: $selectedAddonDetails,
                    totalAmountInCents: $this->totalAmountInCents,
                    monthlyRecurringAmountInCents: $this->monthlyRecurringAmountInCents,
                    subscriptionMonths: $invoicePaymentType === PaymentType::Subscription
                        ? 12
                        : null,
                )
            );

            session()->flash(
                'success',
                'Checkout link generated and emailed successfully.'
            );

            return redirect()->route(
                'invoices.show',
                $invoice
            );
        } catch (\Exception $e) {
            Log::error(
                'Invoice generation error: '.$e->getMessage(),
                [
                    'ticket_id' => $this->ticket?->id,
                    'amount' => $this->amount,
                    'base_is_subscription' => $this->isSubscription,
                    'selected_addons' => $this->selectedAddonPriceIds,
                ]
            );

            $this->addError(
                'submit',
                'An error occurred: '.$e->getMessage()
            );
        }
    }

    public function render()
    {
        return view(
            'livewire.teams.tickets.charge-customer-modal',
            [
                'availableAddons' => $this->availableAddons(),
            ]
        );
    }
}