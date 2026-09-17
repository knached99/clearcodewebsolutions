<?php

namespace App\Livewire\Teams\Tickets;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Mail\InvoiceCheckoutMail;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Services\StripeService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class ChargeCustomerModal extends Component
{
    public bool $show = false;

    public ?Ticket $ticket = null;

    public string $amount = ''; // base amount, user input

    public string $description = 'Development Services'; // base charge desc

    public bool $isSubscription = false;

    // Configurable addons
    public array $availableAddons = [
        'maintenance' => ['name' => 'Website Maintenance', 'price' => 99],
        'seo' => ['name' => 'SEO Optimization', 'price' => 149],
        'monitoring' => ['name' => 'Performance Monitoring', 'price' => 49],
        'security' => ['name' => 'Security Patching', 'price' => 79],
        'content' => ['name' => 'Content Updates', 'price' => 129],
    ];

    public array $selectedAddons = [];

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
    }

    public function resetForm()
    {
        $this->amount = '';
        $this->description = 'Development Services - Ticket #'.($this->ticket?->ticket_number ?? '');
        $this->isSubscription = false;
        $this->selectedAddons = [];
    }

    public function toggleAddon(string $addonKey)
    {
        if (in_array($addonKey, $this->selectedAddons)) {
            $this->selectedAddons = array_diff($this->selectedAddons, [$addonKey]);
        } else {
            $this->selectedAddons[] = $addonKey;
        }
    }

    public function getBaseAmountInCentsProperty(): int
    {
        return (int) (floatval($this->amount) * 100);
    }

    public function getTotalAmountInCentsProperty(): int
    {
        $total = $this->baseAmountInCents;
        foreach ($this->selectedAddons as $key) {
            $total += ($this->availableAddons[$key]['price'] * 100);
        }

        return $total;
    }

    public function submit(StripeService $stripe)
    {
        $this->authorize('update', $this->ticket);

        $this->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        // 1. Resolve Customer (local DB and Stripe)
        $customer = $this->ticket->resolveCustomer();

        $stripeCustomer = $stripe->resolveCustomer($customer->email, $customer->name);
        if (! $customer->stripe_id) {
            $customer->update(['stripe_id' => $stripeCustomer->id]);
        }

        // 2. Create the local Invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $this->ticket->id,
            'total_amount' => 0, // Recalculated by InvoiceItem boot events
            'status' => InvoiceStatus::Pending,
            'payment_type' => $this->isSubscription ? PaymentType::Subscription : PaymentType::OneTime,
            'subscription_months' => $this->isSubscription ? 12 : null,
            'checkout_token' => Str::random(64),
        ]);

        // 3. Create Base Line Item
        $invoice->items()->create([
            'description' => $this->description,
            'amount' => $this->baseAmountInCents,
            'is_subscription' => $this->isSubscription,
        ]);

        // 4. Create Addon Line Items
        foreach ($this->selectedAddons as $key) {
            if (isset($this->availableAddons[$key])) {
                $invoice->items()->create([
                    'description' => $this->availableAddons[$key]['name'],
                    'amount' => $this->availableAddons[$key]['price'] * 100,
                    'is_subscription' => true,
                ]);
            }
        }

        // Note: The Mail class needs to be created next
        if (class_exists(InvoiceCheckoutMail::class)) {
            Mail::to($customer->email)->send(
                new InvoiceCheckoutMail($invoice)
            );
        }

        session()->flash('ticket-success', 'Invoice generated and emailed to the customer successfully.');

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.teams.tickets.charge-customer-modal');
    }
}
