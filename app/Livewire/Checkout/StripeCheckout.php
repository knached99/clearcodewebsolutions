<?php

namespace App\Livewire\Checkout;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\StripeService;
use Livewire\Component;

class StripeCheckout extends Component
{
    public string $token;

    public ?Invoice $invoice = null;

    public ?string $clientSecret = null;

    public ?string $errorMessage = null;

    public function mount(string $token, StripeService $stripe)
    {
        $this->token = $token;
        $this->invoice = Invoice::with(['customer', 'items'])->where('checkout_token', $token)->firstOrFail();

        if ($this->invoice->status === InvoiceStatus::Paid) {
            return;
        }

       try {
    $this->invoice->recalculateTotal();
    $customer = $this->invoice->customer;

    if (! $customer->stripe_id) {
        $customerName = filled(trim((string) $customer->name)) ? trim($customer->name) : 'Valued Customer';
        $stripeCust = $stripe->resolveCustomer($customer->email, $customerName);
        $customer->update(['stripe_id' => $stripeCust->id]);
    }

    $invoiceDescription = 'Payment for Invoice #' . substr($this->invoice->id, 0, 8);

    // Filter items by subscription status
    $recurringItems = $this->invoice->items->where('is_subscription', true);
    $oneTimeItems = $this->invoice->items->where('is_subscription', false);

    // =========================================================================
    // CASE 1: PURE ONE-TIME PAYMENT (No base subscription, no add-ons)
    // =========================================================================
    if ($recurringItems->isEmpty()) {
        $paymentIntent = null;

        if ($this->invoice->stripe_id && str_starts_with($this->invoice->stripe_id, 'pi_')) {
            try {
                $existingPi = $stripe->getPaymentIntent($this->invoice->stripe_id);
                if (in_array($existingPi->status, ['requires_payment_method', 'requires_confirmation', 'requires_action'])) {
                    $paymentIntent = $existingPi;
                }
            } catch (\Exception $e) {}
        }

        if (! $paymentIntent) {
            $paymentIntent = $stripe->createPaymentIntent(
                $this->invoice->total_amount,
                $customer->stripe_id,
                [
                    'invoice_id' => $this->invoice->id,
                    'description' => $invoiceDescription,
                ]
            );
            $this->invoice->update(['stripe_id' => $paymentIntent->id]);
        }

        $this->clientSecret = $paymentIntent->client_secret;

    // =========================================================================
    // CASE 2: HYBRID / SUBSCRIPTION (Has recurring base OR recurring add-ons)
    // =========================================================================
    } else {
        $subscription = null;

        if ($this->invoice->stripe_id && str_starts_with($this->invoice->stripe_id, 'sub_')) {
            try {
                $existingSub = $stripe->getSubscription($this->invoice->stripe_id);
                if (in_array($existingSub->status, ['incomplete', 'active', 'trialing'])) {
                    $subscription = $existingSub;
                }
            } catch (\Exception $e) {}
        }

        if (! $subscription) {
            $priceIds = [];

            // 1. Resolve recurring price IDs (Base split or Add-ons)
            foreach ($recurringItems as $item) {
                if (! empty($item->stripe_price_id)) {
                    $priceIds[] = $item->stripe_price_id;
                    continue;
                }

                $itemDescription = filled(trim((string) $item->description)) 
                    ? trim($item->description) 
                    : 'Subscription Item #' . $item->id;

                $product = $stripe->createProduct($itemDescription);
                $price = $stripe->createPrice($product->id, $item->amount, true);

                if (\Schema::hasColumn($item->getTable(), 'stripe_price_id')) {
                    $item->update(['stripe_price_id' => $price->id]);
                }

                $priceIds[] = $price->id;
            }

            // 2. Format one-time items (e.g., $10,000 Upfront Base) to add to Invoice #1
            $formattedOneTimeItems = [];
            foreach ($oneTimeItems as $item) {
                $formattedOneTimeItems[] = [
                    'amount' => $item->amount,
                    'description' => filled(trim((string) $item->description)) ? trim($item->description) : 'One-Time Charge',
                ];
            }

            // 3. Create Stripe Subscription
            $subscription = $stripe->createSubscription(
                $customer->stripe_id,
                $priceIds,
                [
                    'invoice_id' => $this->invoice->id,
                    'description' => $invoiceDescription,
                ],
                $formattedOneTimeItems
            );

            $this->invoice->update(['stripe_id' => $subscription->id]);
        }

        $this->clientSecret = $stripe->getSubscriptionClientSecret($subscription);
    }

} catch (\Exception $e) {
    $this->errorMessage = 'Unable to initialize checkout. Please contact support. ' . $e->getMessage();
}
    }

    public function render()
    {
        return view('livewire.checkout.stripe-checkout')
            ->layout('layouts.home_layout');
    }
}