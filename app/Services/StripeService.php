<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;
use Stripe\Subscription;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(
            config('services.stripe.secret') ?: 'sk_test_placeholder'
        );
    }

    /**
     * Get or create a Stripe Customer by email.
     */
    public function resolveCustomer(
        string $email,
        string $name
    ): Customer {
        $customers = $this->stripe->customers->search([
            'query' => "email:'{$email}'",
            'limit' => 1,
        ]);

        if (count($customers->data) > 0) {
            return $customers->data[0];
        }

        $params = [
            'email' => $email,
        ];

        $cleanName = trim($name);

        if ($cleanName !== '') {
            $params['name'] = $cleanName;
        }

        return $this->stripe->customers->create($params);
    }

    /**
     * Create a new product in Stripe.
     */
    public function createProduct(
        string $name,
        ?string $description = null
    ): Product {
        $cleanName = trim($name);

        $params = [
            'name' => $cleanName !== ''
                ? $cleanName
                : 'Service Item',
        ];

        $cleanDescription = trim((string) $description);

        if ($cleanDescription !== '') {
            $params['description'] = $cleanDescription;
        }

        return $this->stripe->products->create($params);
    }

    /**
     * Create a price for a product.
     *
     * Amount is in cents.
     */
    public function createPrice(
        string $productId,
        int $amountInCents,
        bool $isRecurring = false
    ): Price {
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
     * Create a PaymentIntent for a one-time charge.
     *
     * Payment details are collected by Stripe Elements.
     * Raw card information never passes through Livewire/Laravel.
     */
    public function createPaymentIntent(
        int $amountInCents,
        string $customerId,
        array $metadata = []
    ): PaymentIntent {
        $params = [
            'amount' => $amountInCents,
            'currency' => 'usd',
            'customer' => $customerId,

            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ];

        if (isset($metadata['description'])) {
            $cleanDesc = trim(
                (string) $metadata['description']
            );

            if ($cleanDesc !== '') {
                $params['description'] = $cleanDesc;
            }

            unset($metadata['description']);
        }

        if (! empty($metadata)) {
            $params['metadata'] = $metadata;
        }

        return $this->stripe
            ->paymentIntents
            ->create($params);
    }

    /**
     * Retrieve an existing PaymentIntent.
     */
    public function getPaymentIntent(
        string $paymentIntentId
    ): PaymentIntent {
        return $this->stripe
            ->paymentIntents
            ->retrieve($paymentIntentId);
    }

    /**
     * Create a subscription, supporting both recurring
     * prices and one-time initial invoice items.
     */
    public function createSubscription(
        string $customerId,
        array $priceIds,
        array $metadata = [],
        array $oneTimeItems = []
    ): Subscription {
        $items = array_map(
            fn ($priceId) => [
                'price' => $priceId,
            ],
            $priceIds
        );

        $params = [
            'customer' => $customerId,

            'items' => $items,

            'payment_behavior' => 'default_incomplete',

            'payment_settings' => [
                'save_default_payment_method' => 'on_subscription',
            ],

            'expand' => [
                'latest_invoice.payment_intent',
                'pending_setup_intent',
            ],
        ];

        /*
         * Attach one-time line items to the first invoice.
         */
        if (! empty($oneTimeItems)) {
            $params['add_invoice_items'] = array_map(
                function ($item) {
                    return [
                        'amount' => $item['amount'],
                        'currency' => 'usd',
                        'description' => $item['description'],
                    ];
                },
                $oneTimeItems
            );
        }

        /*
         * Description is a top-level Stripe property.
         */
        if (isset($metadata['description'])) {
            $cleanDesc = trim(
                (string) $metadata['description']
            );

            if ($cleanDesc !== '') {
                $params['description'] = $cleanDesc;
            }

            unset($metadata['description']);
        }

        if (! empty($metadata)) {
            $params['metadata'] = $metadata;
        }

        return $this->stripe
            ->subscriptions
            ->create($params);
    }

    /**
     * Retrieve an existing Subscription with expanded objects.
     */
    public function getSubscription(
        string $subscriptionId
    ): Subscription {
        return $this->stripe
            ->subscriptions
            ->retrieve(
                $subscriptionId,
                [
                    'expand' => [
                        'latest_invoice.payment_intent',
                        'pending_setup_intent',
                    ],
                ]
            );
    }

    /**
     * Dynamically fetch unique active recurring add-on prices.
     */
    public function getActiveAddons(): array
    {
        $prices = $this->stripe->prices->all([
            'active' => true,
            'type' => 'recurring',
            'expand' => [
                'data.product',
            ],
            'limit' => 100,
        ]);

        $addons = [];
        $seenNames = [];

        foreach ($prices->data as $price) {

            if (
                ! $price->recurring ||
                $price->recurring->interval !== 'month'
            ) {
                continue;
            }

            $product = $price->product;

            if (
                ! is_object($product) ||
                ! $product->active
            ) {
                continue;
            }

            $isAddon =
                ($product->metadata->type ?? null) === 'addon'
                ||
                in_array(
                    $product->name,
                    [
                        'Ongoing Maintenance',
                        'SEO Optimization',
                        'Performance Monitoring',
                        'Security Updates',
                        'Feature Updates',
                    ],
                    true
                );

            if (
                $isAddon &&
                ! in_array(
                    $product->name,
                    $seenNames,
                    true
                )
            ) {
                $seenNames[] = $product->name;

                $addons[$price->id] = [
                    'id' => $price->id,
                    'name' => $product->name,
                    'price' => $price->unit_amount / 100,
                    'price_in_cents' => $price->unit_amount,
                ];
            }
        }

        return $addons;
    }

    /**
     * Safely resolve client_secret across subscription edge cases.
     */
    public function getSubscriptionClientSecret(
        Subscription|string $subscription
    ): string {
        if (is_string($subscription)) {
            $subscription = $this->getSubscription(
                $subscription
            );
        }

        /*
         * Edge Case 1:
         * Subscription uses a pending SetupIntent.
         */
        if (! empty($subscription->pending_setup_intent)) {

            $setupIntent =
                $subscription->pending_setup_intent;

            if (is_string($setupIntent)) {
                $setupIntent =
                    $this->stripe
                        ->setupIntents
                        ->retrieve($setupIntent);
            }

            if (
                is_object($setupIntent) &&
                ! empty($setupIntent->client_secret)
            ) {
                return $setupIntent->client_secret;
            }
        }

        /*
         * Edge Case 2:
         * Resolve latest invoice.
         */
        $latestInvoice =
            $subscription->latest_invoice ?? null;

        if (is_string($latestInvoice)) {
            $latestInvoice =
                $this->stripe
                    ->invoices
                    ->retrieve(
                        $latestInvoice,
                        [
                            'expand' => [
                                'payment_intent',
                            ],
                        ]
                    );
        }

        if (is_object($latestInvoice)) {

            $paymentIntent =
                $latestInvoice->payment_intent ?? null;

            if (is_string($paymentIntent)) {
                $paymentIntent =
                    $this->stripe
                        ->paymentIntents
                        ->retrieve($paymentIntent);
            }

            if (
                is_object($paymentIntent) &&
                ! empty($paymentIntent->client_secret)
            ) {
                return $paymentIntent->client_secret;
            }

            /*
             * Edge Case 3:
             *
             * Invoice exists with amount due but doesn't
             * have a PaymentIntent attached.
             */
            if (
                isset($latestInvoice->amount_due) &&
                $latestInvoice->amount_due > 0
            ) {
                $customerId =
                    is_string($subscription->customer)
                        ? $subscription->customer
                        : $subscription->customer->id;

                $fallbackIntent =
                    $this->stripe
                        ->paymentIntents
                        ->create([
                            'amount' => $latestInvoice->amount_due,

                            'currency' => $latestInvoice->currency
                                    ?? 'usd',

                            'customer' => $customerId,

                            'metadata' => [
                                'subscription_id' => $subscription->id,

                                'invoice_id' => $latestInvoice->id,
                            ],
                        ]);

                if (
                    ! empty(
                        $fallbackIntent->client_secret
                    )
                ) {
                    return $fallbackIntent->client_secret;
                }
            }
        }

        throw new \Exception(
            'Unable to resolve client_secret. '.
            'Please ensure invoice items have an amount greater than 0.'
        );
    }

    /**
     * Send an invoice email.
     */
    public function sendInvoiceEmail(
        string $customerId,
        string $priceId
    ): Invoice {
        $this->stripe->invoiceItems->create([
            'customer' => $customerId,
            'price' => $priceId,
        ]);

        $invoice = $this->stripe->invoices->create([
            'customer' => $customerId,
            'collection_method' => 'send_invoice',
            'days_until_due' => 7,
        ]);

        return $this->stripe
            ->invoices
            ->sendInvoice($invoice->id);
    }

    /**
     * Create a Stripe Checkout link.
     */
    public function createCheckoutLink(
        string $customerId,
        string $priceId
    ): Session {
        return $this->stripe
            ->checkout
            ->sessions
            ->create([
                'customer' => $customerId,

                'payment_method_types' => [
                    'card',
                ],

                'line_items' => [
                    [
                        'price' => $priceId,
                        'quantity' => 1,
                    ],
                ],

                'mode' => 'payment',

                'success_url' => url('/payment-success'),

                'cancel_url' => url('/payment-cancelled'),
            ]);
    }
}
