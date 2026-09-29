<?php

namespace App\Livewire\Checkout;

use App\Mail\PaymentReceived;
use App\Mail\PaymentReceivedAdmin;
use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Models\User;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Services\Checkout\InvoiceSignatureService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Stripe\StripeClient;
use Throwable;

#[Layout('layouts.home_layout')]
class StripeCheckout extends Component
{
    public Invoice $invoice;

    public string $billingName = '';

    public string $billingEmail = '';

    public ?string $clientSecret = null;

    public ?string $paymentIntentId = null;

    public ?string $paymentStatus = null;

    public ?string $checkoutError = null;

    public bool $checkoutSuccessful = false;

    public bool $signatureRequired = false;

    public bool $hasSignedAgreement = false;

    public string $signatureData = '';

    public int $totalInCents = 0;

    public string $formattedTotal = '0.00';

    /**
     * Mount checkout.
     */
    public function mount(string $token): void
    {
        $this->invoice = Invoice::query()
            ->where('checkout_token', $token)
            ->with([
                'items',
                'customer',
                'ticket',
            ])
            ->firstOrFail();

        $this->billingName = trim(
            (string) ($this->invoice->customer?->name ?? '')
        );

        $this->billingEmail = trim(
            (string) ($this->invoice->customer?->email ?? '')
        );

        $signatureService = app(
            InvoiceSignatureService::class
        );

        $this->signatureRequired =
            $signatureService->requiresSignature(
                $this->invoice
            );

        $this->hasSignedAgreement =
            $signatureService->hasSignedAgreement(
                $this->invoice
            );

        $this->totalInCents =
            $this->calculateTotalInCents();

        $this->formattedTotal =
            number_format(
                $this->totalInCents / 100,
                2
            );

        /*
         * Do not create Stripe objects until the mandatory
         * agreement has been signed.
         */
        if (
            $this->signatureRequired
            && ! $this->hasSignedAgreement
        ) {
            return;
        }

        if ($this->totalInCents < 50) {
            $this->checkoutError =
                $this->totalInCents > 0
                    ? 'This invoice amount must be at least $0.50 to accept a Stripe payment.'
                    : 'This invoice has an invalid payment amount.';

            Log::error(
                'Checkout has an invalid Stripe total.',
                [
                    'invoice_id' =>
                        $this->invoice->id,

                    'invoice_total' =>
                        $this->invoice->total,

                    'calculated_total_in_cents' =>
                        $this->totalInCents,
                ]
            );

            return;
        }

        /*
         * Already completed in this browser/session.
         */
        if ($this->hasRememberedSuccessfulCheckout()) {
            $this->checkoutSuccessful = true;
            $this->paymentStatus = 'succeeded';

            return;
        }

        /*
         * Stripe redirect return.
         */
        $returnedPaymentIntentId = trim(
            (string) request()->query(
                'payment_intent',
                ''
            )
        );

        if ($returnedPaymentIntentId !== '') {
            $this->restoreReturnedPaymentIntent(
                $returnedPaymentIntentId
            );

            return;
        }

        /*
         * See whether this invoice has already been paid.
         */
        $successfulPaymentIntent =
            $this->findSuccessfulPaymentIntent();

        if ($successfulPaymentIntent !== null) {
            $this->applySuccessfulPaymentIntent(
                $successfulPaymentIntent
            );

            return;
        }

        /*
         * If a Stripe subscription already exists for this invoice,
         * attempt to restore its initial payment.
         */
        if ($this->invoice->stripe_id) {
            if (
                $this->restoreExistingSubscriptionCheckout()
            ) {
                return;
            }
        }

        $this->createStripeCheckoutIntent();
    }

    /**
     * Return invoice item amount in cents.
     *
     * IMPORTANT:
     * This assumes the database stores these monetary values in cents.
     *
     * Example:
     * 10000 = $100.00
     */
    protected function itemAmountInCents($item): int
    {
        $amount =
            $item->total
            ?? $item->amount
            ?? $item->unit_amount
            ?? $item->price
            ?? 0;

        return (int) round(
            (float) $amount
        );
    }

    /**
     * Calculate local invoice total.
     */
    protected function calculateTotalInCents(): int
    {
        $total = 0;

        foreach ($this->invoice->items as $item) {
            $amount =
                $this->itemAmountInCents($item);

            /*
             * If there is no aggregate total and quantity > 1,
             * treat the stored amount as the unit amount.
             */
            if (
                isset($item->quantity)
                && (int) $item->quantity > 1
                && ! isset($item->total)
            ) {
                $amount *= (int) $item->quantity;
            }

            $total += $amount;
        }

        return $total;
    }

    /**
     * Format item amount for display.
     */
    public function formatItemAmount($item): string
    {
        return number_format(
            $this->itemAmountInCents($item) / 100,
            2
        );
    }

    /**
     * Save mandatory signature.
     */
    public function saveSignature(
        string $signatureData
    ): void {
        $this->signatureData =
            trim($signatureData);

        if ($this->signatureData === '') {
            $this->checkoutError =
                'Please sign the agreement before continuing.';

            return;
        }

        app(InvoiceSignatureService::class)
            ->saveSignature(
                $this->invoice,
                $this->signatureData
            );

        $this->hasSignedAgreement = true;

        $this->checkoutError = null;

        /*
         * Signature has been saved.
         * Now create Stripe checkout.
         */
        $this->createStripeCheckoutIntent();
    }

    /**
     * Stripe client.
     */
    protected function stripe(): StripeClient
    {
        $secret = trim(
            (string) config(
                'services.stripe.secret'
            )
        );

        if ($secret === '') {
            throw new \RuntimeException(
                'Stripe secret key is not configured.'
            );
        }

        return new StripeClient($secret);
    }

    /**
     * Decide whether this is a subscription or one-time checkout.
     */
    protected function createStripeCheckoutIntent(): void 
    {
        $this->createPaymentIntent();
    }

    /**
     * Create a normal one-time PaymentIntent.
     */
    protected function createPaymentIntent(): void
    {
        try {
            $stripe = $this->stripe();

            $email = trim(
                $this->billingEmail
            );

            $customerId =
                $this->resolveStripeCustomerId(
                    $stripe
                );

            $paymentIntentData = [
            'amount' =>
                $this->totalInCents,

            'currency' =>
                'usd',

            'automatic_payment_methods' => [
                'enabled' => true,
            ],

            'description' =>
                'Invoice #' .
                $this->invoice->id,

            'metadata' => [
                'invoice_id' =>
                    (string) $this->invoice->id,

                'checkout_token' =>
                    (string) $this->invoice->checkout_token,
            ],
        ];

        if ($this->invoice->payment_type === PaymentType::Subscription) {
            $paymentIntentData['setup_future_usage'] = 'off_session';
        }

            if ($customerId !== null) {
                $paymentIntentData['customer'] =
                    $customerId;
            }

            if ($email !== '') {
                $paymentIntentData['receipt_email'] =
                    $email;
            }

            $idempotencyKey =
                'checkout-payment-intent-v2' .
                (string) $this->invoice->id;

            $paymentIntent =
                $stripe->paymentIntents->create(
                    $paymentIntentData,
                    [
                        'idempotency_key' =>
                            $idempotencyKey,
                    ]
                );

            $this->paymentIntentId =
                (string) $paymentIntent->id;

            $this->clientSecret =
                (string) $paymentIntent->client_secret;

            $this->paymentStatus =
                (string) $paymentIntent->status;

            $this->checkoutError = null;

            if (
                $this->clientSecret === ''
            ) {
                throw new \RuntimeException(
                    'Stripe did not return a PaymentIntent client secret.'
                );
            }

            /*
             * Tell the browser a client secret is ready.
             */
            $this->dispatch(
                'stripe-checkout-ready',
                clientSecret: $this->clientSecret,
                paymentIntentId: $this->paymentIntentId,
                paymentStatus: $this->paymentStatus,
            );

            Log::info(
                'Stripe PaymentIntent created.',
                [
                    'invoice_id' =>
                        $this->invoice->id,

                    'payment_intent_id' =>
                        $paymentIntent->id,

                    'customer_id' =>
                        $customerId,

                    'amount_in_cents' =>
                        $this->totalInCents,

                    'status' =>
                        $paymentIntent->status,
                ]
            );
        }  catch (Throwable $e) {
            $this->clientSecret = null;
            $this->paymentIntentId = null;
            $this->paymentStatus = null;

            $this->checkoutError =
                'Stripe error: ' . $e->getMessage();

            Log::error(
                'Stripe PaymentIntent initialization failed.',
                [
                    'invoice_id' =>
                        $this->invoice->id ?? null,

                    'amount_in_cents' =>
                        $this->totalInCents,

                    'message' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );
        }
    }

    /**
     * Create subscription checkout.
     *
     * Current Stripe flow:
     *
     * Subscription
     *   -> initial Invoice
     *   -> confirmation_secret
     *   -> PaymentIntent client_secret
     *   -> Stripe Elements
     */
protected function createSubscriptionCheckout(): void
{
    try {
        $stripe = $this->stripe();

        $customerId = $this->resolveStripeCustomerId($stripe);

        if ($customerId === null) {
            throw new \RuntimeException(
                'A Stripe customer could not be resolved for this subscription.'
            );
        }

        $subscriptionItems = [];

        foreach ($this->invoice->items as $item) {
            $amount = $this->itemAmountInCents($item);

            if ($amount <= 0) {
                continue;
            }

            $description = trim(
                (string) (
                    $item->description
                    ?? $item->name
                    ?? 'Invoice item'
                )
            );

            if ($description === '') {
                $description = 'Invoice item';
            }

            $isSubscriptionItem = (bool) (
                $item->is_subscription ?? false
            );

            $isAddon = (bool) (
                $item->is_addon ?? false
            );

            $stripePriceId = trim(
                (string) (
                    $item->stripe_price_id ?? ''
                )
            );

            /*
             * ONLY existing recurring Stripe Prices are allowed
             * into the Stripe Subscription.
             */
            if ($isSubscriptionItem) {
                if ($stripePriceId === '') {
                    throw new \RuntimeException(
                        'Subscription item "' .
                        $description .
                        '" is missing its existing Stripe recurring Price ID.'
                    );
                }

                $stripePrice = $stripe->prices->retrieve(
                    $stripePriceId,
                    []
                );

                if (
                    ($stripePrice->type ?? null) !== 'recurring'
                ) {
                    throw new \RuntimeException(
                        'Stripe price ' .
                        $stripePriceId .
                        ' is not a recurring price.'
                    );
                }

                if (
                    isset($stripePrice->currency)
                    && strtolower(
                        (string) $stripePrice->currency
                    ) !== 'usd'
                ) {
                    throw new \RuntimeException(
                        'Stripe price ' .
                        $stripePriceId .
                        ' is not configured in USD.'
                    );
                }

                /*
                 * Recurring addons use their existing Stripe Price.
                 *
                 * A recurring custom base has no Stripe Price in your
                 * current data model, so it cannot be added to the
                 * Stripe Subscription without creating a Product/Price.
                 */
                $subscriptionItems[] = [
                    'price' => $stripePriceId,
                    'quantity' => max(
                        1,
                        (int) ($item->quantity ?? 1)
                    ),
                ];

                continue;
            }

            /*
             * NEVER convert a one-time/custom base item into a Stripe
             * Price or Product here.
             */
            if (! $isAddon) {
                continue;
            }
        }

        /*
         * There is nothing valid to put into a Stripe subscription.
         */
        if (empty($subscriptionItems)) {
            throw new \RuntimeException(
                'No existing recurring Stripe Prices are available for this subscription.'
            );
        }

        if (count($subscriptionItems) > 20) {
            throw new \RuntimeException(
                'An invoice can contain at most 20 recurring subscription items.'
            );
        }

        $subscriptionData = [
            'customer' => $customerId,

            'items' => $subscriptionItems,

            'collection_method' => 'charge_automatically',

            'payment_behavior' => 'default_incomplete',

            'payment_settings' => [
                'save_default_payment_method' =>
                    'on_subscription',
            ],

            'metadata' => [
                'invoice_id' =>
                    (string) $this->invoice->id,

                'checkout_token' =>
                    (string) $this->invoice->checkout_token,
            ],

            'expand' => [
                'latest_invoice.confirmation_secret',
            ],
        ];

        $idempotencyKey =
            'checkout-subscription-' .
            (string) $this->invoice->id;

        $subscription =
            $stripe->subscriptions->create(
                $subscriptionData,
                [
                    'idempotency_key' =>
                        $idempotencyKey,
                ]
            );

        $this->invoice->update([
            'stripe_id' =>
                $subscription->id,

            'status' =>
                'pending',
        ]);

        $latestInvoice =
            $subscription->latest_invoice ?? null;

        if (is_string($latestInvoice)) {
            $latestInvoice =
                $stripe->invoices->retrieve(
                    $latestInvoice,
                    [
                        'expand' => [
                            'confirmation_secret',
                        ],
                    ]
                );
        }

        if (! is_object($latestInvoice)) {
            throw new \RuntimeException(
                'Stripe created the subscription but did not return its initial invoice.'
            );
        }

        $confirmationSecret =
            $this->getInvoiceConfirmationSecret(
                $latestInvoice
            );

        if ($confirmationSecret === null) {
            $latestInvoice =
                $stripe->invoices->retrieve(
                    $latestInvoice->id,
                    [
                        'expand' => [
                            'confirmation_secret',
                        ],
                    ]
                );

            $confirmationSecret =
                $this->getInvoiceConfirmationSecret(
                    $latestInvoice
                );
        }

        $paymentIntent = null;

        if ($confirmationSecret !== null) {
            $paymentIntent =
                $this->paymentIntentFromClientSecret(
                    $stripe,
                    $confirmationSecret
                );
        }

        if (
            $paymentIntent === null
            && isset($latestInvoice->payment_intent)
            && !empty($latestInvoice->payment_intent)
        ) {
            $legacyPaymentIntent =
                $latestInvoice->payment_intent;

            if (is_string($legacyPaymentIntent)) {
                $paymentIntent =
                    $stripe->paymentIntents->retrieve(
                        $legacyPaymentIntent
                    );
            } elseif (is_object($legacyPaymentIntent)) {
                $paymentIntent =
                    $legacyPaymentIntent;
            }
        }

        Log::info(
            'Stripe subscription initial invoice inspected.',
            [
                'local_invoice_id' =>
                    $this->invoice->id,

                'stripe_subscription_id' =>
                    $subscription->id,

                'stripe_invoice_id' =>
                    $latestInvoice->id ?? null,

                'stripe_invoice_status' =>
                    $latestInvoice->status ?? null,

                'stripe_invoice_amount_due' =>
                    $latestInvoice->amount_due ?? null,

                'confirmation_secret_present' =>
                    $confirmationSecret !== null,

                'payment_intent_found' =>
                    $paymentIntent !== null,
            ]
        );

        $amountDue =
            (int) (
                $latestInvoice->amount_due ?? 0
            );

        if ($amountDue === 0) {
            $this->paymentIntentId = null;

            $this->clientSecret = null;

            $this->paymentStatus = 'succeeded';

            $this->checkoutError = null;

            return;
        }

        if ($paymentIntent === null) {
            throw new \RuntimeException(
                'Stripe created the subscription and initial invoice, but no initial PaymentIntent client secret was returned. Invoice: ' .
                ($latestInvoice->id ?? 'unknown') .
                ', amount_due: ' .
                $amountDue
            );
        }

        if (empty($paymentIntent->client_secret)) {
            throw new \RuntimeException(
                'Stripe returned the subscription PaymentIntent without a client secret.'
            );
        }

        if (
            isset($paymentIntent->amount)
            && isset($latestInvoice->amount_due)
            && (int) $paymentIntent->amount
                !== (int) $latestInvoice->amount_due
        ) {
            throw new \RuntimeException(
                'Stripe PaymentIntent amount does not match the initial subscription invoice amount.'
            );
        }

        $this->paymentIntentId =
            (string) $paymentIntent->id;

        $this->clientSecret =
            (string) $paymentIntent->client_secret;

        $this->paymentStatus =
            (string) (
                $paymentIntent->status
                ?? 'requires_payment_method'
            );

        $this->checkoutError = null;

        $this->dispatch(
            'stripe-checkout-ready',
            clientSecret: $this->clientSecret,
            paymentIntentId: $this->paymentIntentId,
            paymentStatus: $this->paymentStatus,
        );

        Log::info(
            'Stripe Subscription checkout ready.',
            [
                'local_invoice_id' =>
                    $this->invoice->id,

                'stripe_subscription_id' =>
                    $subscription->id,

                'stripe_invoice_id' =>
                    $latestInvoice->id ?? null,

                'payment_intent_id' =>
                    $this->paymentIntentId,

                'payment_intent_status' =>
                    $this->paymentStatus,

                'stripe_invoice_amount_due' =>
                    $amountDue,

                'local_invoice_total' =>
                    $this->totalInCents,
            ]
        );

    } catch (Throwable $e) {
        $this->clientSecret = null;

        $this->paymentIntentId = null;

        $this->paymentStatus = null;

        $this->checkoutError =
            'Stripe could not initialize this subscription checkout. Please try again.';

        Log::error(
            'Stripe Subscription initialization failed.',
            [
                'invoice_id' =>
                    $this->invoice->id ?? null,

                'existing_stripe_id' =>
                    $this->invoice->stripe_id ?? null,

                'message' =>
                    $e->getMessage(),

                'exception' =>
                    get_class($e),
            ]
        );
    }
}

    /**
     * Get confirmation_secret.client_secret from an Invoice.
     *
     * Current Stripe Invoice shape:
     *
     * confirmation_secret: {
     *     client_secret: "pi_..._secret_..."
     * }
     */
    protected function getInvoiceConfirmationSecret(
        object $invoice
    ): ?string {
        $confirmationSecret =
            $invoice->confirmation_secret
            ?? null;

        if (
            is_object(
                $confirmationSecret
            )
        ) {
            $clientSecret =
                $confirmationSecret->client_secret
                ?? null;

            if (
                is_string($clientSecret)
                && trim($clientSecret) !== ''
            ) {
                return trim($clientSecret);
            }
        }

        if (
            is_array(
                $confirmationSecret
            )
        ) {
            $clientSecret =
                $confirmationSecret['client_secret']
                ?? null;

            if (
                is_string($clientSecret)
                && trim($clientSecret) !== ''
            ) {
                return trim($clientSecret);
            }
        }

        /*
         * A few SDK/API combinations can expose the nested property
         * unusually. Support the direct string shape too.
         */
        if (
            is_string(
                $confirmationSecret
            )
            && trim(
                $confirmationSecret
            ) !== ''
        ) {
            return trim(
                $confirmationSecret
            );
        }

        return null;
    }

    /**
     * Turn a PaymentIntent client secret into the actual PaymentIntent.
     */
    protected function paymentIntentFromClientSecret(
        StripeClient $stripe,
        string $clientSecret
    ): mixed {
        $clientSecret =
            trim($clientSecret);

        if ($clientSecret === '') {
            return null;
        }

        /*
         * Current PaymentIntent client secret:
         *
         * pi_xxxxxxxxx_secret_xxxxxxxxx
         */
        $parts =
            explode(
                '_secret_',
                $clientSecret,
                2
            );

        if (
            count($parts) !== 2
            || ! str_starts_with(
                $parts[0],
                'pi_'
            )
        ) {
            throw new \RuntimeException(
                'Stripe returned an invalid PaymentIntent client secret.'
            );
        }

        $paymentIntentId =
            $parts[0];

        return $stripe
            ->paymentIntents
            ->retrieve(
                $paymentIntentId
            );
    }

  
    /**
     * Extract initial PaymentIntent from a subscription.
     *
     * Current API:
     *   latest_invoice.confirmation_secret.client_secret
     *
     * Legacy fallback:
     *   latest_invoice.payment_intent
     */
    protected function extractSubscriptionPaymentIntent(
        StripeClient $stripe,
        $subscription
    ): mixed {
        $latestInvoice =
            $subscription->latest_invoice
            ?? null;

        if (
            is_string(
                $latestInvoice
            )
        ) {
            $latestInvoice =
                $stripe->invoices->retrieve(
                    $latestInvoice,
                    [
                        'expand' => [
                            'confirmation_secret',
                        ],
                    ]
                );
        }

        if (
            ! is_object(
                $latestInvoice
            )
        ) {
            return null;
        }

        $clientSecret =
            $this->getInvoiceConfirmationSecret(
                $latestInvoice
            );

        if ($clientSecret !== null) {
            return $this->paymentIntentFromClientSecret(
                $stripe,
                $clientSecret
            );
        }

        /*
         * Legacy fallback.
         */
        if (
            isset(
                $latestInvoice->payment_intent
            )
            && ! empty(
                $latestInvoice->payment_intent
            )
        ) {
            $paymentIntent =
                $latestInvoice->payment_intent;

            if (
                is_string(
                    $paymentIntent
                )
            ) {
                return $stripe
                    ->paymentIntents
                    ->retrieve(
                        $paymentIntent
                    );
            }

            if (
                is_object(
                    $paymentIntent
                )
            ) {
                return $paymentIntent;
            }
        }

        return null;
    }

    /**
     * Restore an already-created subscription.
     */
    protected function restoreExistingSubscriptionCheckout(): bool
    {
        try {
            $stripe = $this->stripe();

            $subscription =
                $stripe->subscriptions->retrieve(
                    (string) $this->invoice->stripe_id,
                    [
                        'expand' => [
                            'latest_invoice.confirmation_secret',
                        ],
                    ]
                );

            $subscriptionStatus =
                (string) (
                    $subscription->status
                    ?? ''
                );

            /*
             * Expired incomplete subscriptions cannot be reused.
             */
            if (
                $subscriptionStatus
                === 'incomplete_expired'
            ) {
                Log::warning(
                    'Existing Stripe subscription expired.',
                    [
                        'invoice_id' =>
                            $this->invoice->id,

                        'subscription_id' =>
                            $subscription->id,
                    ]
                );

                $this->invoice->update([
                    'stripe_id' =>
                        null,

                    'status' =>
                        'pending',
                ]);

                return false;
            }

            /*
             * Get the initial payment.
             */
            $paymentIntent =
                $this->extractSubscriptionPaymentIntent(
                    $stripe,
                    $subscription
                );

            /*
             * Already paid.
             */
            if (
                $paymentIntent !== null
                && (
                    $paymentIntent->status
                    ?? null
                ) === 'succeeded'
            ) {
                $this->applySuccessfulPaymentIntent(
                    $paymentIntent
                );

                return true;
            }

            /*
             * Existing incomplete subscription needs payment.
             */
            if (
                $paymentIntent !== null
                && ! empty(
                    $paymentIntent->client_secret
                )
            ) {
                $this->paymentIntentId =
                    (string) $paymentIntent->id;

                $this->clientSecret =
                    (string) $paymentIntent->client_secret;

                $this->paymentStatus =
                    (string) (
                        $paymentIntent->status
                        ?? 'requires_payment_method'
                    );

                $this->checkoutError =
                    null;

                $this->dispatch(
                'stripe-checkout-ready',
                clientSecret: $this->clientSecret,
                paymentIntentId: $this->paymentIntentId,
                paymentStatus: $this->paymentStatus,
            );

                Log::info(
                    'Existing Stripe subscription checkout restored.',
                    [
                        'invoice_id' =>
                            $this->invoice->id,

                        'subscription_id' =>
                            $subscription->id,

                        'payment_intent_id' =>
                            $this->paymentIntentId,

                        'payment_status' =>
                            $this->paymentStatus,
                    ]
                );

                return true;
            }

            /*
             * Active subscription can indicate that no payment is
             * currently required.
             */
            if (
                $subscriptionStatus
                === 'active'
            ) {
                Log::info(
                    'Existing Stripe subscription is active.',
                    [
                        'invoice_id' =>
                            $this->invoice->id,

                        'subscription_id' =>
                            $subscription->id,
                    ]
                );

                return false;
            }

            return false;

        } catch (Throwable $e) {
            Log::warning(
                'Could not restore existing Stripe subscription checkout.',
                [
                    'invoice_id' =>
                        $this->invoice->id
                        ?? null,

                    'subscription_id' =>
                        $this->invoice->stripe_id
                        ?? null,

                    'message' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),
                ]
            );

            return false;
        }
    }

    /**
     * Find/create Stripe Customer.
     */
    protected function resolveStripeCustomerId(
        StripeClient $stripe
    ): ?string {
        $email =
            trim(
                (string) $this->billingEmail
            );

        $name =
            trim(
                (string) $this->billingName
            );

        if (
            $email === ''
            && $name === ''
        ) {
            return null;
        }

        /*
         * Search by email.
         */
        if ($email !== '') {
            $searchEmail =
                str_replace(
                    ['\\', "'"],
                    ['\\\\', "\\'"],
                    $email
                );

            $customers =
                $stripe->customers->search([
                    'query' =>
                        "email:'{$searchEmail}'",

                    'limit' =>
                        1,
                ]);

            if (
                isset(
                    $customers->data[0]
                )
            ) {
                $customer =
                    $customers->data[0];

                $update = [];

                if (
                    $name !== ''
                    && (
                        $customer->name
                        ?? null
                    ) !== $name
                ) {
                    $update['name'] =
                        $name;
                }

                if (
                    ($customer->email ?? null)
                    !== $email
                ) {
                    $update['email'] =
                        $email;
                }

                if ($update !== []) {
                    $customer =
                        $stripe->customers->update(
                            $customer->id,
                            $update
                        );
                }

                return (string) $customer->id;
            }

            $customerData = [
                'email' =>
                    $email,
            ];

            if ($name !== '') {
                $customerData['name'] =
                    $name;
            }

            $customer =
                $stripe->customers->create(
                    $customerData,
                    [
                        'idempotency_key' =>
                            'checkout-customer-' .
                            (string) $this->invoice->id,
                    ]
                );

            return (string) $customer->id;
        }

        if ($name !== '') {
            $customer =
                $stripe->customers->create(
                    [
                        'name' =>
                            $name,
                    ],
                    [
                        'idempotency_key' =>
                            'checkout-customer-' .
                            (string) $this->invoice->id,
                    ]
                );

            return (string) $customer->id;
        }

        return null;
    }

    /**
     * Restore a PaymentIntent returned by Stripe.
     */
    protected function restoreReturnedPaymentIntent(
        string $paymentIntentId
    ): void {
        try {
            $stripe = $this->stripe();

            $paymentIntent =
                $stripe->paymentIntents->retrieve(
                    trim($paymentIntentId),
                    []
                );

            $metadataInvoiceId =
                (string) (
                    $paymentIntent
                        ->metadata
                        ->invoice_id
                        ?? ''
                );

            if (
                $metadataInvoiceId !== ''
                && $metadataInvoiceId
                    !== (string) $this->invoice->id
            ) {
                throw new \RuntimeException(
                    'The returned Stripe payment does not belong to this invoice.'
                );
            }

            if (
                isset($paymentIntent->amount)
                && (int) $paymentIntent->amount
                    !== $this->totalInCents
            ) {
                throw new \RuntimeException(
                    'The returned Stripe payment amount does not match this invoice.'
                );
            }

            if (
                isset(
                    $paymentIntent->currency
                )
                && strtolower(
                    (string) $paymentIntent->currency
                ) !== 'usd'
            ) {
                throw new \RuntimeException(
                    'The returned Stripe payment currency does not match this invoice.'
                );
            }

            $this->paymentIntentId =
                (string) $paymentIntent->id;

            $this->clientSecret =
                $paymentIntent->client_secret;

            $this->paymentStatus =
                $paymentIntent->status;

            $this->checkoutError =
                null;

            if (
                empty(
                    $this->clientSecret
                )
            ) {
                throw new \RuntimeException(
                    'Stripe did not return a client secret for the returned payment.'
                );
            }

            if (
                (string) $paymentIntent->status
                === 'succeeded'
            ) {
                $this->applySuccessfulPaymentIntent(
                    $paymentIntent
                );
            } else {
                $this->checkoutSuccessful =
                    false;
            }

            Log::info(
                'Returned Stripe PaymentIntent restored.',
                [
                    'invoice_id' =>
                        $this->invoice->id,

                    'payment_intent_id' =>
                        $paymentIntent->id,

                    'status' =>
                        $paymentIntent->status,
                ]
            );
        } catch (Throwable $e) {
            $this->clientSecret = null;

            $this->paymentIntentId = null;

            $this->paymentStatus = null;

            $this->checkoutError =
                'We could not verify the returned Stripe payment. Please start the payment again.';

            Log::error(
                'Returned Stripe PaymentIntent verification failed.',
                [
                    'invoice_id' =>
                        $this->invoice->id
                        ?? null,

                    'payment_intent_id' =>
                        $paymentIntentId,

                    'message' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),
                ]
            );
        }
    }

    /**
     * Session success flag.
     */
    protected function hasRememberedSuccessfulCheckout(): bool
    {
        return (bool) session()->get(
            $this->successfulCheckoutSessionKey(),
            false
        );
    }

    /**
     * Successful checkout session key.
     */
    protected function successfulCheckoutSessionKey(): string
    {
        return 'checkout.success.' .
            $this->invoice->id;
    }

    /**
     * Search Stripe for a successful payment associated with this invoice.
     */
    protected function findSuccessfulPaymentIntent(): mixed
    {
        /*
         * First inspect existing subscription.
         */
        if ($this->invoice->stripe_id) {
            try {
                $stripe =
                    $this->stripe();

                $subscription =
                    $stripe->subscriptions->retrieve(
                        (string) $this->invoice->stripe_id,
                        [
                            'expand' => [
                                'latest_invoice.confirmation_secret',
                            ],
                        ]
                    );

                $paymentIntent =
                    $this->extractSubscriptionPaymentIntent(
                        $stripe,
                        $subscription
                    );

                if (
                    $paymentIntent !== null
                    && (
                        $paymentIntent->status
                        ?? null
                    ) === 'succeeded'
                ) {
                    return $paymentIntent;
                }

            } catch (Throwable $e) {
                Log::warning(
                    'Could not inspect existing Stripe subscription for successful payment.',
                    [
                        'invoice_id' =>
                            $this->invoice->id
                            ?? null,

                        'subscription_id' =>
                            $this->invoice->stripe_id,

                        'message' =>
                            $e->getMessage(),
                    ]
                );
            }
        }

        /*
         * Fallback to PaymentIntent metadata search.
         */
        try {
            $invoiceId =
                str_replace(
                    "'",
                    "\\'",
                    (string) $this->invoice->id
                );

            $results =
                $this->stripe()
                    ->paymentIntents
                    ->search([
                        'query' =>
                            "metadata['invoice_id']:'{$invoiceId}' AND status:'succeeded'",

                        'limit' =>
                            1,
                    ]);

            return $results->data[0]
                ?? null;

        } catch (Throwable $e) {
            Log::warning(
                'Could not search Stripe for a previously successful checkout.',
                [
                    'invoice_id' =>
                        $this->invoice->id
                        ?? null,

                    'message' =>
                        $e->getMessage(),
                ]
            );

            return null;
        }
    }

    protected function createRecurringAddonSubscription(
    mixed $paymentIntent
): void {
    /*
     * The custom Development Services base is intentionally NOT
     * included because it has no Stripe Price.
     *
     * Only existing Stripe recurring addon Prices are included.
     */
    $stripe = $this->stripe();

    $customerId = $paymentIntent->customer ?? null;

    if (is_object($customerId)) {
        $customerId = $customerId->id ?? null;
    }

    if (! is_string($customerId) || trim($customerId) === '') {
        throw new \RuntimeException(
            'A Stripe customer is required to create recurring add-ons.'
        );
    }

    $subscriptionItems = [];

    foreach ($this->invoice->items as $item) {
        if (! (bool) ($item->is_subscription ?? false)) {
            continue;
        }

        /*
         * No Price means this is the custom recurring base.
         * It is intentionally excluded from Stripe Billing.
         */
        $priceId = trim(
            (string) ($item->stripe_price_id ?? '')
        );

        if ($priceId === '') {
            continue;
        }

        $price = $stripe->prices->retrieve(
            $priceId,
            []
        );

        if (($price->active ?? false) !== true) {
            throw new \RuntimeException(
                "Stripe Price {$priceId} is inactive."
            );
        }

        if (($price->type ?? null) !== 'recurring') {
            throw new \RuntimeException(
                "Stripe Price {$priceId} is not recurring."
            );
        }

        if (
            strtolower(
                (string) ($price->currency ?? '')
            ) !== 'usd'
        ) {
            throw new \RuntimeException(
                "Stripe Price {$priceId} is not USD."
            );
        }

        $subscriptionItems[] = [
            'price' => $priceId,
            'quantity' => max(
                1,
                (int) ($item->quantity ?? 1)
            ),
        ];
    }

    /*
     * There may be no Stripe-managed recurring add-ons.
     * That's valid because the base recurring amount is local.
     */
    if ($subscriptionItems === []) {
        Log::info(
            'No Stripe recurring add-ons to subscribe.',
            [
                'invoice_id' =>
                    $this->invoice->id,
            ]
        );

        return;
    }

    /*
     * The customer already paid today's invoice.
     *
     * Start the Stripe add-on subscription one month later
     * so Stripe does not immediately charge the add-ons again.
     */
    $trialEnd = now()
        ->addMonth()
        ->timestamp;

    $paymentMethodId =
        $paymentIntent->payment_method ?? null;

    if (is_object($paymentMethodId)) {
        $paymentMethodId =
            $paymentMethodId->id ?? null;
    }

    $subscriptionData = [
        'customer' => $customerId,

        'items' => $subscriptionItems,

        'collection_method' => 'charge_automatically',

        'trial_end' => $trialEnd,

        'metadata' => [
            'invoice_id' =>
                (string) $this->invoice->id,

            'checkout_token' =>
                (string) $this->invoice->checkout_token,

            'source' =>
                'checkout_addons',
        ],

        'payment_settings' => [
            'save_default_payment_method' =>
                'on_subscription',
        ],
    ];

    if (
        is_string($paymentMethodId)
        && $paymentMethodId !== ''
    ) {
        $subscriptionData['default_payment_method'] =
            $paymentMethodId;
    }

    $subscription = $stripe
        ->subscriptions
        ->create(
            $subscriptionData,
            [
                'idempotency_key' =>
                    'checkout-addon-subscription-' .
                    (string) $this->invoice->id,
            ]
        );

    /*
     * Store the actual Stripe Subscription ID.
     *
     * NEVER replace it with the PaymentIntent ID.
     */
    $this->invoice->stripe_id =
        (string) $subscription->id;

    $this->invoice->save();

    Log::info(
        'Stripe recurring addon subscription created.',
        [
            'invoice_id' =>
                $this->invoice->id,

            'subscription_id' =>
                $subscription->id,

            'payment_intent_id' =>
                $paymentIntent->id,

            'addon_item_count' =>
                count($subscriptionItems),

            'trial_end' =>
                $trialEnd,
        ]
    );
}

    /**
     * Mark successful payment.
     */
    protected function applySuccessfulPaymentIntent(
        mixed $paymentIntent
    ): void {
        $this->paymentStatus =
            'succeeded';

        $this->paymentIntentId =
            (string) $paymentIntent->id;

        $this->invoice->status = InvoiceStatus::Paid;

            /*
            * stripe_id has different meanings:
            *
            * One-time invoice:
            *     pi_...
            *
            * Subscription invoice:
            *     sub_...
            *
            * The PaymentIntent ID is already tracked separately in
            * $this->paymentIntentId and in Stripe metadata.
            */
            if (
                $this->invoice->payment_type !==
                PaymentType::Subscription
            ) {
                $this->invoice->stripe_id =
                    (string) $paymentIntent->id;
            }

            $this->invoice->save();

        $this->checkoutSuccessful =
            true;

        $this->checkoutError =
            null;

        session()->put(
            $this->successfulCheckoutSessionKey(),
            true
        );

        $email =
            trim(
                (string) (
                    $this->billingEmail
                    ?: (
                        $paymentIntent
                            ->receipt_email
                        ?? ''
                    )
                )
            );

        if ($email !== '') {
            $this->billingEmail =
                $email;
        }

        $this->sendCompletionEmails(
            $paymentIntent
        );

        Log::info(
            'Stripe checkout marked successful.',
            [
                'invoice_id' =>
                    $this->invoice->id,

                'payment_intent_id' =>
                    $paymentIntent->id,
            ]
        );
    }

    /**
     * Send completion emails with lock.
     */
    protected function sendCompletionEmails(
        mixed $paymentIntent
    ): void {
        if (
            (string) (
                $paymentIntent->status
                ?? ''
            ) !== 'succeeded'
        ) {
            return;
        }

        $paymentIntentId =
            (string) $paymentIntent->id;

        $lock =
            Cache::lock(
                'checkout-email:' .
                $this->invoice->id .
                ':' .
                $paymentIntentId,
                60
            );

        if (! $lock->get()) {
            Log::info(
                'Checkout email lock already held.',
                [
                    'invoice_id' =>
                        $this->invoice->id,

                    'payment_intent_id' =>
                        $paymentIntentId,
                ]
            );

            return;
        }

        try {
            $customerEmail =
                trim(
                    (string) (
                        $this->billingEmail
                        ?: (
                            $paymentIntent
                                ->receipt_email
                            ?? ''
                        )
                    )
                );

            /*
             * Customer email.
             */
            if ($customerEmail !== '') {
                try {
                    Mail::to(
                        $customerEmail
                    )->send(
                        new PaymentReceived(
                            $this->invoice
                        )
                    );

                    Log::info(
                        'Customer payment email sent.',
                        [
                            'invoice_id' =>
                                $this->invoice->id,

                            'payment_intent_id' =>
                                $paymentIntentId,

                            'email' =>
                                $customerEmail,
                        ]
                    );
                } catch (Throwable $e) {
                    Log::error(
                        'Customer payment email failed.',
                        [
                            'invoice_id' =>
                                $this->invoice->id,

                            'payment_intent_id' =>
                                $paymentIntentId,

                            'email' =>
                                $customerEmail,

                            'message' =>
                                $e->getMessage(),

                            'exception' =>
                                get_class($e),
                        ]
                    );
                }
            }

            /*
             * Admin emails.
             */
            $adminEmails =
                $this->getCheckoutAdminEmails();

            Log::info(
                'Checkout admin email recipients resolved.',
                [
                    'invoice_id' =>
                        $this->invoice->id,

                    'payment_intent_id' =>
                        $paymentIntentId,

                    'recipients' =>
                        $adminEmails,
                ]
            );

            foreach ($adminEmails as $adminEmail) {
                if (
                    $customerEmail !== ''
                    && strtolower(
                        $adminEmail
                    ) === strtolower(
                        $customerEmail
                    )
                ) {
                    continue;
                }

                try {
                    Mail::to(
                        $adminEmail
                    )->send(
                        new PaymentReceivedAdmin(
                            $this->invoice
                        )
                    );

                    Log::info(
                        'Admin payment email sent.',
                        [
                            'invoice_id' =>
                                $this->invoice->id,

                            'payment_intent_id' =>
                                $paymentIntentId,

                            'email' =>
                                $adminEmail,
                        ]
                    );
                } catch (Throwable $e) {
                    Log::error(
                        'Admin payment email failed.',
                        [
                            'invoice_id' =>
                                $this->invoice->id,

                            'payment_intent_id' =>
                                $paymentIntentId,

                            'email' =>
                                $adminEmail,

                            'message' =>
                                $e->getMessage(),

                            'exception' =>
                                get_class($e),
                        ]
                    );
                }
            }

        } catch (Throwable $e) {
            Log::error(
                'Checkout completion email processing failed.',
                [
                    'invoice_id' =>
                        $this->invoice->id
                        ?? null,

                    'payment_intent_id' =>
                        $paymentIntentId,

                    'message' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),
                ]
            );

        } finally {
            $lock->release();
        }
    }

    /**
     * Get checkout admin email recipients.
     */
    protected function getCheckoutAdminEmails(): array
    {
        $emails = [];

        /*
         * Configured recipients.
         */
        $configured =
            config(
                'mail.checkout_admin_emails',
                []
            );

        if (
            is_string(
                $configured
            )
        ) {
            $configured =
                preg_split(
                    '/\s*,\s*/',
                    $configured,
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );
        }

        foreach (
            (array) $configured
            as $email
        ) {
            $email =
                trim(
                    (string) $email
                );

            if (
                $email !== ''
                && filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                $emails[] =
                    $email;
            }
        }

        /*
         * users.role.
         */
        try {
            if (
                Schema::hasColumn(
                    'users',
                    'role'
                )
            ) {
                $roleEmails =
                    User::query()
                        ->whereIn(
                            'role',
                            [
                                'owner',
                                'admin',
                            ]
                        )
                        ->whereNotNull(
                            'email'
                        )
                        ->pluck(
                            'email'
                        )
                        ->map(
                            fn ($email) =>
                                trim(
                                    (string) $email
                                )
                        )
                        ->filter(
                            fn ($email) =>
                                filter_var(
                                    $email,
                                    FILTER_VALIDATE_EMAIL
                                )
                        )
                        ->all();

                $emails =
                    array_merge(
                        $emails,
                        $roleEmails
                    );
            }

        } catch (Throwable $e) {
            Log::warning(
                'Could not load checkout admin users by role column.',
                [
                    'invoice_id' =>
                        $this->invoice->id
                        ?? null,

                    'message' =>
                        $e->getMessage(),
                ]
            );
        }

        /*
         * roles relationship.
         */
        try {
            if (
                method_exists(
                    User::class,
                    'roles'
                )
            ) {
                $roleEmails =
                    User::query()
                        ->whereHas(
                            'roles',
                            function ($query) {
                                $query->whereIn(
                                    'name',
                                    [
                                        'owner',
                                        'admin',
                                    ]
                                );
                            }
                        )
                        ->whereNotNull(
                            'email'
                        )
                        ->pluck(
                            'email'
                        )
                        ->map(
                            fn ($email) =>
                                trim(
                                    (string) $email
                                )
                        )
                        ->filter(
                            fn ($email) =>
                                filter_var(
                                    $email,
                                    FILTER_VALIDATE_EMAIL
                                )
                        )
                        ->all();

                $emails =
                    array_merge(
                        $emails,
                        $roleEmails
                    );
            }

        } catch (Throwable $e) {
            Log::warning(
                'Could not load checkout admin users by roles relationship.',
                [
                    'invoice_id' =>
                        $this->invoice->id
                        ?? null,

                    'message' =>
                        $e->getMessage(),
                ]
            );
        }

        return collect($emails)
            ->map(
                fn ($email) =>
                    strtolower(
                        trim(
                            (string) $email
                        )
                    )
            )
            ->filter(
                fn ($email) =>
                    filter_var(
                        $email,
                        FILTER_VALIDATE_EMAIL
                    )
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Validate billing information.
     */
    public function validateBilling(): bool
    {
        $this->validate([
            'billingName' => [
                'required',
                'string',
                'max:255',
            ],

            'billingEmail' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        return true;
    }

    /**
     * Verify a PaymentIntent after frontend confirmation.
     */
    public function paymentCompleted(
        string $paymentIntentId
    ): string {
        $paymentIntentId =
            trim(
                $paymentIntentId
            );

        if (
            $paymentIntentId === ''
            || $paymentIntentId
                !== $this->paymentIntentId
        ) {
            throw new \RuntimeException(
                'The Stripe payment could not be verified.'
            );
        }

        $stripe =
            $this->stripe();

        $paymentIntent =
            $stripe->paymentIntents->retrieve(
                $paymentIntentId,
                []
            );

        /*
         * Metadata check is optional because subscription-created
         * PaymentIntents may not carry the Subscription metadata.
         */
        $metadataInvoiceId =
            (string) (
                $paymentIntent
                    ->metadata
                    ->invoice_id
                    ?? ''
            );

        if (
            $metadataInvoiceId !== ''
            && $metadataInvoiceId
                !== (string) $this->invoice->id
        ) {
            throw new \RuntimeException(
                'The Stripe payment does not belong to this invoice.'
            );
        }

        /*
         * Verify amount.
         */
        if (
            isset(
                $paymentIntent->amount
            )
            && (int) $paymentIntent->amount
                !== $this->totalInCents
        ) {
            throw new \RuntimeException(
                'The Stripe payment amount does not match this invoice.'
            );
        }

        /*
         * Verify currency.
         */
        if (
            isset(
                $paymentIntent->currency
            )
            && strtolower(
                (string) $paymentIntent->currency
            ) !== 'usd'
        ) {
            throw new \RuntimeException(
                'The Stripe payment currency does not match this invoice.'
            );
        }

        $this->paymentStatus =
            (string) $paymentIntent->status;

                if (
                (string) $paymentIntent->status
                === 'succeeded'
            ) {
                /*
                * The initial invoice is paid in full first.
                *
                * Any Stripe-managed recurring add-ons are started
                * separately after the successful payment.
                */
                if (
                    $this->invoice->payment_type ===
                    PaymentType::Subscription
                ) {
                    $this->createRecurringAddonSubscription(
                        $paymentIntent
                    );
                }

                $this->applySuccessfulPaymentIntent(
                    $paymentIntent
                );
            } else {
                $this->checkoutSuccessful = false;
            }

        Log::info(
            'Stripe checkout payment status verified.',
            [
                'invoice_id' =>
                    $this->invoice->id,

                'payment_intent_id' =>
                    $paymentIntent->id,

                'status' =>
                    $paymentIntent->status,

                'amount_in_cents' =>
                    $paymentIntent->amount,
            ]
        );

        return (string) $paymentIntent->status;
    }

    /**
     * Render checkout view.
     */
    public function render()
    {
        return view(
            'livewire.checkout.stripe-checkout'
        );
    }
}