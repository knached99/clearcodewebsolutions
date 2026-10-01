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
use Illuminate\Support\Str;
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

        return Invoice::query()
            ->where('ticket_id', $this->ticket->id)
            ->latest('id')
            ->first();
    }

    #[Computed]
    public function availableAddons(): array
    {
        return Cache::remember(
            'stripe_active_addons',
            3600,
            function () {
                try {
                    return app(StripeService::class)
                        ->getActiveAddons();
                } catch (\Exception $e) {
                    Log::error(
                        'Failed to fetch Stripe add-ons: '
                        .$e->getMessage()
                    );

                    return [];
                }
            }
        );
    }

    #[Computed]
    public function baseAmountInCents(): int
    {
        if (empty($this->amount)) {
            return 0;
        }

        return (int) round(
            (float) $this->amount * 100
        );
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
        $monthlyBase = $this->isSubscription
            ? intdiv($this->baseAmountInCents(), 12)
            : $this->baseAmountInCents();

        return $monthlyBase
            + $this->addonsMonthlyAmountInCents();
    }

    /**
     * Determines whether the invoice contains ANY recurring items.
     *
     * This is separate from the invoice payment type.
     *
     * Recurring items can exist because:
     * - the base charge is recurring
     * - one or more monthly addons are selected
     */
    // public function hasRecurringInvoiceItems(): bool
    // {
    //     return $this->isSubscription
    //         || ! empty($this->selectedAddonPriceIds);
    // }

    /**
     * Determines the invoice payment type.
     *
     * IMPORTANT:
     * Only the BASE charge controls whether the invoice itself
     * is classified as a subscription.
     *
     * Recurring addons do NOT turn a one-time base invoice
     * into subscription revenue.
     */
    public function resolveInvoicePaymentType(): PaymentType
    {
        return $this->isSubscription
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
            ? intdiv($this->baseAmountInCents(), 12)
            : 0;

        return $monthlyBase
            + $this->addonsMonthlyAmountInCents();
    }

    #[On('open-charge-modal')]
    public function openModal(int $ticketId)
    {
        $this->ticket = Ticket::findOrFail($ticketId);

        $this->authorize(
            'update',
            $this->ticket
        );

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

        $this->description =
            'Development Services - Ticket #'
            .($this->ticket?->ticket_number ?? 'N/A');

        $this->isSubscription = false;

        $this->selectedAddonPriceIds = [];

        $this->resetErrorBag();
    }

    public function submit(StripeService $stripe)
    {
        $this->authorize(
            'update',
            $this->ticket
        );

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
            |--------------------------------------------------------------------------
            | Invoice Payment Type
            |--------------------------------------------------------------------------
            |
            | ONLY the base charge determines this.
            |
            | Base one-time + recurring addon:
            |     payment_type = one_time
            |
            | Base recurring + recurring addon:
            |     payment_type = subscription
            |
            */

            $invoicePaymentType =
                $this->resolveInvoicePaymentType();

            $invoice = new Invoice;

            $invoice->ticket_id =
                $this->ticket->id;

            $invoice->customer_id =
                $customer->id ?? null;

            $invoice->checkout_token =
                Str::random(32);

            /*
            |--------------------------------------------------------------------------
            | Amount Due Today
            |--------------------------------------------------------------------------
            |
            | base amount
            | +
            | first month's recurring addon charges
            |
            */

            $invoice->total_amount =
                $this->totalAmountInCents;

            /*
            |--------------------------------------------------------------------------
            | Overall Invoice Type
            |--------------------------------------------------------------------------
            |
            | This is based ONLY on the base charge.
            |
            */

            $invoice->payment_type =
                $invoicePaymentType;

            /*
            |--------------------------------------------------------------------------
            | Subscription Minimum
            |--------------------------------------------------------------------------
            */

            $invoice->subscription_months =
                $invoicePaymentType === PaymentType::Subscription
                    ? 12
                    : null;

            $invoice->save();

            /*
            |--------------------------------------------------------------------------
            | BASE CHARGE
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | The base item gets its own recurring state.
            |
            */

            $baseItem = $invoice->items()->create([
                'description' => $this->description
                    .(
                        $this->isSubscription
                            ? ' (Monthly - 12 Mo. Minimum)'
                            : ' (One-Time)'
                    ),

                'amount' => $this->isSubscription
                ? intdiv($this->baseAmountInCents(), 12)
                : $this->baseAmountInCents(),

                /*
                 * Base charge's own billing type.
                 */
                'is_subscription' => $this->isSubscription,

                /*
                 * This is the BASE item, never an addon.
                 */
                'is_addon' => false,

                /*
                 * Base custom amount does not use a Stripe recurring price.
                 */
                'stripe_price_id' => null,
            ]);

            /*
             * Ensure is_addon is saved even if InvoiceItem has
             * guarded/mass-assignment restrictions.
             */
            $baseItem->forceFill([
                'is_addon' => false,
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | MONTHLY ADDONS
            |--------------------------------------------------------------------------
            |
            | Every selected addon is recurring monthly.
            |
            */

            $addons = $this->availableAddons();

            $selectedAddonDetails = [];

            foreach ($this->selectedAddonPriceIds as $priceId) {
                if (! isset($addons[$priceId])) {
                    continue;
                }

                $addon = $addons[$priceId];

                $selectedAddonDetails[] = $addon;

                $addonItem = $invoice->items()->create([
                    'description' => $addon['name'],

                    'amount' => $addon['price_in_cents'],

                    /*
                     * Addons are ALWAYS recurring.
                     */
                    'is_subscription' => true,

                    /*
                     * This explicitly marks the item as an addon.
                     */
                    'is_addon' => true,

                    /*
                     * Addons use their Stripe recurring Price ID.
                     */
                    'stripe_price_id' => $priceId,
                ]);

                /*
                 * Ensure is_addon is saved even if InvoiceItem has
                 * guarded/mass-assignment restrictions.
                 */
                $addonItem->forceFill([
                    'is_addon' => true,
                ])->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Secure Checkout URL
            |--------------------------------------------------------------------------
            */

            $customCheckoutUrl = route(
                'checkout.show',
                [
                    'token' => $invoice->checkout_token,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Send Checkout Email
            |--------------------------------------------------------------------------
            |
            | isSubscription describes the BASE charge specifically.
            |
            | monthlyRecurringAmountInCents contains:
            | - recurring base amount, when applicable
            | - recurring addons
            |
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

                    subscriptionMonths: $this->isSubscription
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
                'Invoice generation error: '
                .$e->getMessage(),
                [
                    'ticket_id' => $this->ticket?->id,

                    'amount' => $this->amount,

                    'base_is_subscription' => $this->isSubscription,

                    'selected_addons' => $this->selectedAddonPriceIds,
                ]
            );

            $this->addError(
                'submit',
                'An error occurred: '
                .$e->getMessage()
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
