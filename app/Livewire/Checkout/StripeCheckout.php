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
            return; // Show already paid view
        }

        try {
            // Recalculate just in case
            $this->invoice->recalculateTotal();
            $amount = $this->invoice->total_amount;

            // Ensure customer has a stripe_id
            $customer = $this->invoice->customer;
            if (! $customer->stripe_id) {
                $stripeCust = $stripe->resolveCustomer($customer->email, $customer->name);
                $customer->update(['stripe_id' => $stripeCust->id]);
            }

            if (! $this->invoice->isSubscription()) {
                // One-time payment intent
                $paymentIntent = $stripe->createPaymentIntent(
                    $amount,
                    $customer->stripe_id,
                    ['invoice_id' => $this->invoice->id]
                );

                $this->clientSecret = $paymentIntent->client_secret;
                $this->invoice->update(['stripe_id' => $paymentIntent->id]);

            } else {
                // Subscription setup
                $priceIds = [];

                foreach ($this->invoice->items as $item) {
                    // Create product
                    $product = $stripe->createProduct($item->description);
                    // Create price
                    $price = $stripe->createPrice($product->id, $item->amount, true);
                    $priceIds[] = $price->id;
                }

                // Create subscription
                $subscription = $stripe->createSubscription(
                    $customer->stripe_id,
                    $priceIds,
                    ['invoice_id' => $this->invoice->id]
                );

                $this->invoice->update(['stripe_id' => $subscription->id]);

                // Get the payment intent from the first incomplete invoice
                $this->clientSecret = $subscription->latest_invoice->payment_intent->client_secret;
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Unable to initialize checkout. Please contact support. '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.checkout.stripe-checkout')
            ->layout('layouts.home_layout'); // use the public layout
    }
}
