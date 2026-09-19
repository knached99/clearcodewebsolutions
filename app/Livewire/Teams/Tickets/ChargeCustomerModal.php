<?php

namespace App\Livewire\Teams\Tickets;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Mail\InvoiceCheckoutMail;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Services\StripeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        // Cache for 1 hour to eliminate repeated Stripe API network calls on re-renders
        return Cache::remember('stripe_active_addons', 3600, function () {
            try {
                return app(StripeService::class)->getActiveAddons();
            } catch (\Exception $e) {
                Log::error('Failed to fetch Stripe add-ons: ' . $e->getMessage());
                return [];
            }
        });
    }

    #[Computed]
    public function baseAmountInCents(): int
    {
        return (int) round((float) $this->amount * 100);
    }

    #[Computed]
    public function addonsMonthlyAmountInCents(): int
    {
        $total = 0;
        $addons = $this->availableAddons;

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
        $baseToday = $this->isSubscription
            ? (int) round($this->baseAmountInCents / 12)
            : $this->baseAmountInCents;

        return $baseToday + $this->addonsMonthlyAmountInCents;
    }

    #[Computed]
    public function monthlyRecurringAmountInCents(): int
    {
        $monthlyBase = $this->isSubscription
            ? (int) round($this->baseAmountInCents / 12)
            : 0;

        return $monthlyBase + $this->addonsMonthlyAmountInCents;
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

            $customerName = filled(trim((string) $customer->name)) ? trim($customer->name) : 'Valued Customer';
            $stripeCustomer = $stripe->resolveCustomer($customer->email, $customerName);

            if (! $customer->stripe_id && isset($stripeCustomer->id)) {
                $customer->update(['stripe_id' => $stripeCustomer->id]);
            }

            $addons = $this->availableAddons;

            $invoice = DB::transaction(function () use ($customer, $addons) {
                $invoice = Invoice::create([
                    'customer_id' => $customer->id,
                    'ticket_id' => $this->ticket->id,
                    'total_amount' => 0,
                    'status' => InvoiceStatus::Pending,
                    'payment_type' => $this->isSubscription
                        ? PaymentType::Subscription
                        : PaymentType::OneTime,
                    'subscription_months' => $this->isSubscription ? 12 : null,
                    'checkout_token' => Str::random(64),
                ]);

                $baseItemAmount = $this->isSubscription
                    ? (int) round($this->baseAmountInCents / 12)
                    : $this->baseAmountInCents;

                $cleanBaseDescription = filled(trim((string) $this->description))
                    ? trim($this->description)
                    : 'Development Services';

                $invoice->items()->create([
                    'description' => $cleanBaseDescription . ($this->isSubscription ? ' (Monthly - 12 Mo. Minimum)' : ''),
                    'amount' => $baseItemAmount,
                    'is_subscription' => $this->isSubscription,
                ]);

                foreach ($this->selectedAddonPriceIds as $priceId) {
                    if (isset($addons[$priceId])) {
                        $invoice->items()->create([
                            'description' => $addons[$priceId]['name'],
                            'amount' => $addons[$priceId]['price_in_cents'],
                            'is_subscription' => true,
                            'stripe_price_id' => $priceId,
                        ]);
                    }
                }

                $invoice->recalculateTotal();

                return $invoice;
            });

            $invoice->refresh();
            $invoice->load(['customer', 'ticket', 'items']);

            Mail::to($customer->email)->send(new InvoiceCheckoutMail($invoice));

            session()->flash('success', 'Invoice generated and emailed successfully.');

            return $this->redirect(route('invoices.show', $invoice), navigate: true);

        } catch (\Exception $e) {
            Log::error('Invoice generation error: ' . $e->getMessage());
            $this->addError('submit', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.teams.tickets.charge-customer-modal', [
            'availableAddons' => $this->availableAddons,
        ]);
    }
}