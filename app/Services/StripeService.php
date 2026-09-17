<?php

namespace App\Services;

use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;
use Stripe\Subscription;
use Stripe\Invoice;
use Stripe\Checkout\Session;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret') ?: 'sk_test_placeholder');
    }

    /**
     * Get or create a Stripe Customer by email.
     */
    public function resolveCustomer(string $email, string $name): Customer
    {
        $customers = $this->stripe->customers->search([
            'query' => "email:'{$email}'",
            'limit' => 1,
        ]);

        if (count($customers->data) > 0) {
            return $customers->data[0];
        }

        return $this->stripe->customers->create([
            'email' => $email,
            'name' => $name,
        ]);
    }

    /**
     * Create a new product in Stripe.
     */
    public function createProduct(string $name, ?string $description = null): Product
    {
        return $this->stripe->products->create([
            'name' => $name,
            'description' => $description,
        ]);
    }

    /**
     * Create a price for a product. Amount is in cents.
     */
    public function createPrice(string $productId, int $amountInCents, bool $isRecurring = false): Price
    {
        $priceData = [
            'product' => $productId,
            'unit_amount' => $amountInCents,
            'currency' => 'usd',
        ];

        if ($isRecurring) {
            $priceData['recurring'] = [
                'interval' => 'month',
            ];
        }

        return $this->stripe->prices->create($priceData);
    }

    /**
     * Create a payment intent for a one-time charge.
     */
    public function createPaymentIntent(int $amountInCents, string $customerId, array $metadata = []): PaymentIntent
    {
        return $this->stripe->paymentIntents->create([
            'amount' => $amountInCents,
            'currency' => 'usd',
            'customer' => $customerId,
            'metadata' => $metadata,
            'setup_future_usage' => 'off_session', // to save card if requested later
        ]);
    }

    /**
     * Create a subscription. Requires payment method to be set up first,
     * or use `incomplete` status and a SetupIntent / PaymentIntent approach.
     * We'll create it incomplete so we can pay the first invoice on the frontend.
     */
    public function createSubscription(string $customerId, array $priceIds, array $metadata = []): Subscription
    {
        $items = array_map(fn ($priceId) => ['price' => $priceId], $priceIds);

        return $this->stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => $items,
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
            'metadata' => $metadata,
        ]);
    }

    /**
     * OPTION 1: Create and send a Stripe-hosted Invoice.
     * Stripe will automatically email the customer a link to pay.
     */
    public function sendInvoiceEmail(string $customerId, string $priceId): Invoice
    {
        // 1. Attach the price to the customer as an invoice item
        $this->stripe->invoiceItems->create([
            'customer' => $customerId,
            'price' => $priceId,
        ]);

        // 2. Draft the invoice
        $invoice = $this->stripe->invoices->create([
            'customer' => $customerId,
            'collection_method' => 'send_invoice',
            'days_until_due' => 7, // Required when sending invoices
        ]);

        // 3. Trigger Stripe to send the email
        return $this->stripe->invoices->sendInvoice($invoice->id);
    }

    /**
     * OPTION 2: Generate a Checkout Session URL.
     * You will need to email the returned $session->url using Laravel Mail.
     */
    public function createCheckoutLink(string $customerId, string $priceId): Session
    {
        return $this->stripe->checkout->sessions->create([
            'customer' => $customerId,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/payment-success'), // Update with your frontend route
            'cancel_url' => url('/payment-cancelled'), // Update with your frontend route
        ]);
    }
}