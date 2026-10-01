<?php

namespace App\Livewire\Checkout;

use App\Enums\PaymentType;
use App\Mail\PaymentReceived;
use App\Mail\PaymentReceivedAdmin;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Checkout\InvoiceSignatureService;
use App\Services\StripeWebhookService;
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

    public function mount(string $token): void
    {
        $this->invoice = Invoice::query()
            ->where('checkout_token', $token)
            ->with(['items', 'customer', 'ticket'])
            ->firstOrFail();

        $this->billingName = trim((string) ($this->invoice->customer?->name ?? ''));
        $this->billingEmail = trim((string) ($this->invoice->customer?->email ?? ''));

        $signatureService = app(InvoiceSignatureService::class);
        $this->signatureRequired = $signatureService->requiresSignature($this->invoice);
        $this->hasSignedAgreement = $signatureService->hasSignedAgreement($this->invoice);

        $this->totalInCents = $this->calculateTotalInCents();
        $this->formattedTotal = number_format($this->totalInCents / 100, 2);

        if ($this->signatureRequired && ! $this->hasSignedAgreement) {
            return;
        }

        if ($this->totalInCents < 50) {
            $this->checkoutError = $this->totalInCents > 0
                ? 'This invoice amount must be at least $0.50 to accept a Stripe payment.'
                : 'This invoice has an invalid payment amount.';

            Log::error('Checkout has an invalid Stripe total.', [
                'invoice_id' => $this->invoice->id,
                'invoice_total' => $this->invoice->total,
                'calculated_total_in_cents' => $this->totalInCents,
            ]);

            return;
        }

        /*
         * The browser session flag is only a UX optimization. It must never
         * be the source of truth for the payment ledger.
         */
        if ($this->hasRememberedSuccessfulCheckout()) {
            $successfulPaymentIntent = $this->findSuccessfulPaymentIntent();

            if (
                $successfulPaymentIntent !== null
                && ($successfulPaymentIntent->status ?? null) === 'succeeded'
            ) {
                $this->applySuccessfulPaymentIntent($successfulPaymentIntent);
            } else {
                $this->checkoutSuccessful = true;
                $this->paymentStatus = 'succeeded';
            }

            return;
        }

        $returnedPaymentIntentId = trim((string) request()->query('payment_intent', ''));

        if ($returnedPaymentIntentId !== '') {
            $this->restoreReturnedPaymentIntent($returnedPaymentIntentId);

            return;
        }

        $successfulPaymentIntent = $this->findSuccessfulPaymentIntent();

        if ($successfulPaymentIntent !== null) {
            $this->applySuccessfulPaymentIntent($successfulPaymentIntent);

            return;
        }

        if ($this->invoice->stripe_subscription_id) {
            if ($this->restoreExistingSubscriptionCheckout()) {
                return;
            }
        }

        $this->createStripeCheckoutIntent();
    }

    protected function itemAmountInCents($item): int
    {
        $amount = $item->total
            ?? $item->amount
            ?? $item->unit_amount
            ?? $item->price
            ?? 0;

        return (int) round((float) $amount);
    }

    protected function calculateTotalInCents(): int
    {
        $total = 0;

        foreach ($this->invoice->items as $item) {
            $amount = $this->itemAmountInCents($item);

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

    public function formatItemAmount($item): string
    {
        return number_format($this->itemAmountInCents($item) / 100, 2);
    }

    public function saveSignature(string $signatureData): void
    {
        $this->signatureData = trim($signatureData);

        if ($this->signatureData === '') {
            $this->checkoutError = 'Please sign the agreement before continuing.';

            return;
        }

        try {
            app(InvoiceSignatureService::class)->saveSignature(
                $this->invoice,
                $this->signatureData
            );

            $this->hasSignedAgreement = true;
            $this->checkoutError = null;
            $this->createStripeCheckoutIntent();
        } catch (Throwable $e) {
            $this->checkoutError = 'We could not save your agreement. Please try again.';

            Log::error('Checkout signature save failed.', [
                'invoice_id' => $this->invoice->id ?? null,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }
    }

    protected function stripe(): StripeClient
    {
        $secret = trim((string) config('services.stripe.secret'));

        if ($secret === '') {
            throw new \RuntimeException('Stripe secret key is not configured.');
        }

        return new StripeClient($secret);
    }

    protected function createStripeCheckoutIntent(): void
    {
        $this->createPaymentIntent();
    }

    protected function createPaymentIntent(): void
    {
        try {
            $stripe = $this->stripe();
            $email = trim($this->billingEmail);
            $customerId = $this->resolveStripeCustomerId($stripe);

             $checkoutIp = request()->ip();

            Log::info('Checkout IP captured before Stripe PaymentIntent creation.', [
                'invoice_id' => $this->invoice->id,
                'ip' => $checkoutIp,
                'user_agent' => request()->userAgent(),
            ]);

            $paymentIntentData = [
            'amount' => $this->totalInCents,
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'description' => 'Invoice #'.$this->invoice->id,
            'metadata' => [
                'invoice_id' => (string) $this->invoice->id,
                'checkout_token' => (string) $this->invoice->checkout_token,
                'checkout_ip' => $checkoutIp,
            ],
        ];

            if ($this->invoice->payment_type === PaymentType::Subscription) {
                $paymentIntentData['setup_future_usage'] = 'off_session';
            }

            if ($customerId !== null) {
                $paymentIntentData['customer'] = $customerId;
            }

            if ($email !== '') {
                $paymentIntentData['receipt_email'] = $email;
            }

            $paymentIntent = $stripe->paymentIntents->create(
                $paymentIntentData,
                [
                    'idempotency_key' => 'checkout-payment-intent-v2-'.$this->invoice->id,
                ]
            );
            
            Log::info('Stripe PaymentIntent metadata verified.', [
            'payment_intent_id' => $paymentIntent->id,
            'invoice_id' => $this->invoice->id,
            'checkout_ip' => data_get(
                $paymentIntent,
                'metadata.checkout_ip'
            ),
        ]);

            $this->paymentIntentId = (string) ($paymentIntent->id ?? '');
            $this->clientSecret = (string) ($paymentIntent->client_secret ?? '');
            $this->paymentStatus = (string) ($paymentIntent->status ?? '');
            $this->checkoutError = null;

            if ($this->paymentIntentId === '' || $this->clientSecret === '') {
                throw new \RuntimeException('Stripe did not return a valid PaymentIntent or client secret.');
            }

            $this->dispatch(
                'stripe-checkout-ready',
                clientSecret: $this->clientSecret,
                paymentIntentId: $this->paymentIntentId,
                paymentStatus: $this->paymentStatus,
            );

            Log::info('Stripe PaymentIntent created.', [
                'invoice_id' => $this->invoice->id,
                'payment_intent_id' => $paymentIntent->id,
                'customer_id' => $customerId,
                'amount_in_cents' => $this->totalInCents,
                'status' => $paymentIntent->status ?? null,
            ]);
        } catch (Throwable $e) {
            $this->clientSecret = null;
            $this->paymentIntentId = null;
            $this->paymentStatus = null;
            $this->checkoutError = 'Stripe could not initialize this payment. Please try again.';

            Log::error('Stripe PaymentIntent initialization failed.', [
                'invoice_id' => $this->invoice->id ?? null,
                'amount_in_cents' => $this->totalInCents,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }
    }

    protected function createSubscriptionCheckout(): void
    {
        try {
            $stripe = $this->stripe();
            $customerId = $this->resolveStripeCustomerId($stripe);

            if ($customerId === null) {
                throw new \RuntimeException('A Stripe customer could not be resolved for this subscription.');
            }

            $subscriptionItems = [];

            foreach ($this->invoice->items as $item) {
                $amount = $this->itemAmountInCents($item);
                if ($amount <= 0) {
                    continue;
                }

                $description = trim((string) ($item->description ?? $item->name ?? 'Invoice item'));
                $description = $description !== '' ? $description : 'Invoice item';
                $isSubscriptionItem = (bool) ($item->is_subscription ?? false);
                $isAddon = (bool) ($item->is_addon ?? false);
                $stripePriceId = trim((string) ($item->stripe_price_id ?? ''));

                if ($isSubscriptionItem) {
                    if ($stripePriceId === '') {
                        throw new \RuntimeException(
                            'Subscription item "'.$description.'" is missing its existing Stripe recurring Price ID.'
                        );
                    }

                    $stripePrice = $stripe->prices->retrieve($stripePriceId, []);

                    if (($stripePrice->active ?? false) !== true) {
                        throw new \RuntimeException('Stripe price '.$stripePriceId.' is inactive.');
                    }

                    if (($stripePrice->type ?? null) !== 'recurring') {
                        throw new \RuntimeException('Stripe price '.$stripePriceId.' is not a recurring price.');
                    }

                    if (
                        isset($stripePrice->currency)
                        && strtolower((string) $stripePrice->currency) !== 'usd'
                    ) {
                        throw new \RuntimeException('Stripe price '.$stripePriceId.' is not configured in USD.');
                    }

                    $subscriptionItems[] = [
                        'price' => $stripePriceId,
                        'quantity' => max(1, (int) ($item->quantity ?? 1)),
                    ];

                    continue;
                }

                if (! $isAddon) {
                    continue;
                }
            }

            if ($subscriptionItems === []) {
                throw new \RuntimeException('No existing recurring Stripe Prices are available for this subscription.');
            }

            if (count($subscriptionItems) > 20) {
                throw new \RuntimeException('An invoice can contain at most 20 recurring subscription items.');
            }

            $subscriptionData = [
                'customer' => $customerId,
                'items' => $subscriptionItems,
                'collection_method' => 'charge_automatically',
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
                'metadata' => [
                    'invoice_id' => (string) $this->invoice->id,
                    'checkout_token' => (string) $this->invoice->checkout_token,
                ],
                'expand' => ['latest_invoice.confirmation_secret'],
            ];

            $subscription = $stripe->subscriptions->create(
                $subscriptionData,
                [
                    'idempotency_key' => 'checkout-subscription-'.$this->invoice->id,
                ]
            );

            $this->invoice->update([
                'stripe_subscription_id' => (string) $subscription->id,
                'status' => 'pending',
            ]);

            $latestInvoice = $subscription->latest_invoice ?? null;
            if (is_string($latestInvoice)) {
                $latestInvoice = $stripe->invoices->retrieve(
                    $latestInvoice,
                    ['expand' => ['confirmation_secret']]
                );
            }

            if (! is_object($latestInvoice)) {
                throw new \RuntimeException('Stripe created the subscription but did not return its initial invoice.');
            }

            $confirmationSecret = $this->getInvoiceConfirmationSecret($latestInvoice);

            if ($confirmationSecret === null) {
                $latestInvoice = $stripe->invoices->retrieve(
                    $latestInvoice->id,
                    ['expand' => ['confirmation_secret']]
                );
                $confirmationSecret = $this->getInvoiceConfirmationSecret($latestInvoice);
            }

            $paymentIntent = null;

            if ($confirmationSecret !== null) {
                $paymentIntent = $this->paymentIntentFromClientSecret($stripe, $confirmationSecret);
            }

            if (
                $paymentIntent === null
                && isset($latestInvoice->payment_intent)
                && ! empty($latestInvoice->payment_intent)
            ) {
                $legacyPaymentIntent = $latestInvoice->payment_intent;
                $paymentIntent = is_string($legacyPaymentIntent)
                    ? $stripe->paymentIntents->retrieve($legacyPaymentIntent)
                    : (is_object($legacyPaymentIntent) ? $legacyPaymentIntent : null);
            }

            $amountDue = (int) ($latestInvoice->amount_due ?? 0);

            if ($amountDue === 0) {
                $this->paymentIntentId = null;
                $this->clientSecret = null;
                $this->paymentStatus = 'succeeded';
                $this->checkoutError = null;

                return;
            }

            if ($paymentIntent === null) {
                throw new \RuntimeException(
                    'Stripe created the subscription and initial invoice, but no initial PaymentIntent was returned. Invoice: '.
                    ($latestInvoice->id ?? 'unknown').', amount_due: '.$amountDue
                );
            }

            if (empty($paymentIntent->client_secret)) {
                throw new \RuntimeException('Stripe returned the subscription PaymentIntent without a client secret.');
            }

            if (
                isset($paymentIntent->amount)
                && (int) $paymentIntent->amount !== $amountDue
            ) {
                throw new \RuntimeException('Stripe PaymentIntent amount does not match the initial subscription invoice amount.');
            }

            $this->paymentIntentId = (string) $paymentIntent->id;
            $this->clientSecret = (string) $paymentIntent->client_secret;
            $this->paymentStatus = (string) ($paymentIntent->status ?? 'requires_payment_method');
            $this->checkoutError = null;

            $this->dispatch(
                'stripe-checkout-ready',
                clientSecret: $this->clientSecret,
                paymentIntentId: $this->paymentIntentId,
                paymentStatus: $this->paymentStatus,
            );

            Log::info('Stripe Subscription checkout ready.', [
                'local_invoice_id' => $this->invoice->id,
                'stripe_subscription_id' => $subscription->id,
                'stripe_invoice_id' => $latestInvoice->id ?? null,
                'payment_intent_id' => $this->paymentIntentId,
                'payment_intent_status' => $this->paymentStatus,
                'stripe_invoice_amount_due' => $amountDue,
                'local_invoice_total' => $this->totalInCents,
            ]);
        } catch (Throwable $e) {
            $this->clientSecret = null;
            $this->paymentIntentId = null;
            $this->paymentStatus = null;
            $this->checkoutError = 'Stripe could not initialize this subscription checkout. Please try again.';

            Log::error('Stripe Subscription initialization failed.', [
                'invoice_id' => $this->invoice->id ?? null,
                'existing_stripe_subscription_id' => $this->invoice->stripe_subscription_id ?? null,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }
    }

    protected function getInvoiceConfirmationSecret(object $invoice): ?string
    {
        $confirmationSecret = $invoice->confirmation_secret ?? null;

        if (is_object($confirmationSecret)) {
            $clientSecret = $confirmationSecret->client_secret ?? null;

            return is_string($clientSecret) && trim($clientSecret) !== ''
                ? trim($clientSecret)
                : null;
        }

        if (is_array($confirmationSecret)) {
            $clientSecret = $confirmationSecret['client_secret'] ?? null;

            return is_string($clientSecret) && trim($clientSecret) !== ''
                ? trim($clientSecret)
                : null;
        }

        if (is_string($confirmationSecret) && trim($confirmationSecret) !== '') {
            return trim($confirmationSecret);
        }

        return null;
    }

    protected function paymentIntentFromClientSecret(
        StripeClient $stripe,
        string $clientSecret
    ): mixed {
        $clientSecret = trim($clientSecret);

        if ($clientSecret === '') {
            return null;
        }

        $parts = explode('_secret_', $clientSecret, 2);

        if (count($parts) !== 2 || ! str_starts_with($parts[0], 'pi_')) {
            throw new \RuntimeException('Stripe returned an invalid PaymentIntent client secret.');
        }

        return $stripe->paymentIntents->retrieve($parts[0]);
    }

    protected function extractSubscriptionPaymentIntent(
        StripeClient $stripe,
        $subscription
    ): mixed {
        $latestInvoice = $subscription->latest_invoice ?? null;

        if (is_string($latestInvoice)) {
            $latestInvoice = $stripe->invoices->retrieve(
                $latestInvoice,
                ['expand' => ['confirmation_secret']]
            );
        }

        if (! is_object($latestInvoice)) {
            return null;
        }

        $clientSecret = $this->getInvoiceConfirmationSecret($latestInvoice);
        if ($clientSecret !== null) {
            return $this->paymentIntentFromClientSecret($stripe, $clientSecret);
        }

        $paymentIntent = $latestInvoice->payment_intent ?? null;
        if (is_string($paymentIntent) && trim($paymentIntent) !== '') {
            return $stripe->paymentIntents->retrieve(trim($paymentIntent));
        }

        return is_object($paymentIntent) ? $paymentIntent : null;
    }

    protected function restoreExistingSubscriptionCheckout(): bool
    {
        try {
            $stripe = $this->stripe();
            $subscription = $stripe->subscriptions->retrieve(
                (string) $this->invoice->stripe_subscription_id,
                ['expand' => ['latest_invoice.confirmation_secret']]
            );

            $subscriptionStatus = (string) ($subscription->status ?? '');

            if ($subscriptionStatus === 'incomplete_expired') {
                Log::warning('Existing Stripe subscription expired.', [
                    'invoice_id' => $this->invoice->id,
                    'subscription_id' => $subscription->id,
                ]);

                $this->invoice->update([
                    'stripe_subscription_id' => null,
                    'status' => 'pending',
                ]);

                return false;
            }

            $paymentIntent = $this->extractSubscriptionPaymentIntent($stripe, $subscription);

            if (
                $paymentIntent !== null
                && ($paymentIntent->status ?? null) === 'succeeded'
            ) {
                $this->applySuccessfulPaymentIntent($paymentIntent);

                return true;
            }

            if ($paymentIntent !== null && ! empty($paymentIntent->client_secret)) {
                $this->paymentIntentId = (string) $paymentIntent->id;
                $this->clientSecret = (string) $paymentIntent->client_secret;
                $this->paymentStatus = (string) ($paymentIntent->status ?? 'requires_payment_method');
                $this->checkoutError = null;

                $this->dispatch(
                    'stripe-checkout-ready',
                    clientSecret: $this->clientSecret,
                    paymentIntentId: $this->paymentIntentId,
                    paymentStatus: $this->paymentStatus,
                );

                Log::info('Existing Stripe subscription checkout restored.', [
                    'invoice_id' => $this->invoice->id,
                    'subscription_id' => $subscription->id,
                    'payment_intent_id' => $this->paymentIntentId,
                    'payment_status' => $this->paymentStatus,
                ]);

                return true;
            }

            if ($subscriptionStatus === 'active') {
                Log::info('Existing Stripe subscription is active.', [
                    'invoice_id' => $this->invoice->id,
                    'subscription_id' => $subscription->id,
                ]);
            }

            return false;
        } catch (Throwable $e) {
            Log::warning('Could not restore existing Stripe subscription checkout.', [
                'invoice_id' => $this->invoice->id ?? null,
                'subscription_id' => $this->invoice->stripe_subscription_id ?? null,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return false;
        }
    }

    protected function resolveStripeCustomerId(StripeClient $stripe): ?string
    {
        $email = trim((string) $this->billingEmail);
        $name = trim((string) $this->billingName);

        if ($email === '' && $name === '') {
            return null;
        }

        if ($email !== '') {
            $searchEmail = str_replace(['\\', "'"], ['\\\\', "\\'"], $email);

            $customers = $stripe->customers->search([
                'query' => "email:'{$searchEmail}'",
                'limit' => 1,
            ]);

            if (isset($customers->data[0])) {
                $customer = $customers->data[0];
                $update = [];

                if ($name !== '' && ($customer->name ?? null) !== $name) {
                    $update['name'] = $name;
                }

                if (($customer->email ?? null) !== $email) {
                    $update['email'] = $email;
                }

                if ($update !== []) {
                    $customer = $stripe->customers->update($customer->id, $update);
                }

                return (string) $customer->id;
            }

            $customerData = ['email' => $email];
            if ($name !== '') {
                $customerData['name'] = $name;
            }

            $customer = $stripe->customers->create(
                $customerData,
                ['idempotency_key' => 'checkout-customer-'.$this->invoice->id]
            );

            return (string) $customer->id;
        }

        $customer = $stripe->customers->create(
            ['name' => $name],
            ['idempotency_key' => 'checkout-customer-'.$this->invoice->id]
        );

        return (string) $customer->id;
    }

    protected function restoreReturnedPaymentIntent(string $paymentIntentId): void
    {
        try {
            $stripe = $this->stripe();
            $paymentIntentId = trim($paymentIntentId);
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, []);

            $metadataInvoiceId = (string) ($paymentIntent->metadata->invoice_id ?? '');

            if ($metadataInvoiceId !== '' && $metadataInvoiceId !== (string) $this->invoice->id) {
                throw new \RuntimeException('The returned Stripe payment does not belong to this invoice.');
            }

            if (
                isset($paymentIntent->amount)
                && (int) $paymentIntent->amount !== $this->totalInCents
            ) {
                throw new \RuntimeException('The returned Stripe payment amount does not match this invoice.');
            }

            if (
                isset($paymentIntent->currency)
                && strtolower((string) $paymentIntent->currency) !== 'usd'
            ) {
                throw new \RuntimeException('The returned Stripe payment currency does not match this invoice.');
            }

            $this->paymentIntentId = (string) $paymentIntent->id;
            $this->clientSecret = (string) ($paymentIntent->client_secret ?? '');
            $this->paymentStatus = (string) ($paymentIntent->status ?? '');
            $this->checkoutError = null;

            /* A succeeded PI does not need a client secret to restore success. */
            if (($paymentIntent->status ?? null) === 'succeeded') {
                $this->applySuccessfulPaymentIntent($paymentIntent);
            } elseif ($this->clientSecret === '') {
                throw new \RuntimeException('Stripe did not return a client secret for the returned payment.');
            } else {
                $this->checkoutSuccessful = false;
            }

            Log::info('Returned Stripe PaymentIntent restored.', [
                'invoice_id' => $this->invoice->id,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status ?? null,
            ]);
        } catch (Throwable $e) {
            $this->clientSecret = null;
            $this->paymentIntentId = null;
            $this->paymentStatus = null;
            $this->checkoutSuccessful = false;
            $this->checkoutError = 'We could not verify the returned Stripe payment. Please start the payment again.';

            Log::error('Returned Stripe PaymentIntent verification failed.', [
                'invoice_id' => $this->invoice->id ?? null,
                'payment_intent_id' => $paymentIntentId,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }
    }

    protected function hasRememberedSuccessfulCheckout(): bool
    {
        return (bool) session()->get($this->successfulCheckoutSessionKey(), false);
    }

    protected function successfulCheckoutSessionKey(): string
    {
        return 'checkout.success.'.$this->invoice->id;
    }

    protected function findSuccessfulPaymentIntent(): mixed
    {
        if ($this->invoice->stripe_subscription_id) {
            try {
                $stripe = $this->stripe();
                $subscription = $stripe->subscriptions->retrieve(
                    (string) $this->invoice->stripe_subscription_id,
                    ['expand' => ['latest_invoice.confirmation_secret']]
                );

                $paymentIntent = $this->extractSubscriptionPaymentIntent($stripe, $subscription);
                if (
                    $paymentIntent !== null
                    && ($paymentIntent->status ?? null) === 'succeeded'
                ) {
                    return $paymentIntent;
                }
            } catch (Throwable $e) {
                Log::warning('Could not inspect existing Stripe subscription for successful payment.', [
                    'invoice_id' => $this->invoice->id ?? null,
                    'subscription_id' => $this->invoice->stripe_subscription_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        try {
            $invoiceId = str_replace("'", "\\'", (string) $this->invoice->id);

            $results = $this->stripe()->paymentIntents->search([
                'query' => "metadata['invoice_id']:'{$invoiceId}' AND status:'succeeded'",
                'limit' => 10,
            ]);

            foreach ($results->data ?? [] as $paymentIntent) {
                if (($paymentIntent->status ?? null) === 'succeeded') {
                    return $paymentIntent;
                }
            }
        } catch (Throwable $e) {
            Log::warning('Could not search Stripe for a previously successful checkout.', [
                'invoice_id' => $this->invoice->id ?? null,
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    protected function createRecurringAddonSubscription(mixed $paymentIntent): void
    {
        $stripe = $this->stripe();
        $customerId = $paymentIntent->customer ?? null;

        if (is_object($customerId)) {
            $customerId = $customerId->id ?? null;
        }

        if (! is_string($customerId) || trim($customerId) === '') {
            throw new \RuntimeException('A Stripe customer is required to create recurring add-ons.');
        }

        $subscriptionItems = [];

        foreach ($this->invoice->items as $item) {
            if (! (bool) ($item->is_subscription ?? false)) {
                continue;
            }

            $priceId = trim((string) ($item->stripe_price_id ?? ''));
            if ($priceId === '') {
                continue;
            }

            $price = $stripe->prices->retrieve($priceId, []);

            if (($price->active ?? false) !== true) {
                throw new \RuntimeException("Stripe Price {$priceId} is inactive.");
            }

            if (($price->type ?? null) !== 'recurring') {
                throw new \RuntimeException("Stripe Price {$priceId} is not recurring.");
            }

            if (strtolower((string) ($price->currency ?? '')) !== 'usd') {
                throw new \RuntimeException("Stripe Price {$priceId} is not USD.");
            }

            $subscriptionItems[] = [
                'price' => $priceId,
                'quantity' => max(1, (int) ($item->quantity ?? 1)),
            ];
        }

        if ($subscriptionItems === []) {
            Log::info('No Stripe recurring add-ons to subscribe.', [
                'invoice_id' => $this->invoice->id,
            ]);

            return;
        }

        if (count($subscriptionItems) > 20) {
            throw new \RuntimeException('An invoice can contain at most 20 recurring subscription items.');
        }

        $trialEnd = now()->addMonth()->timestamp;
        $paymentMethodId = $paymentIntent->payment_method ?? null;

        if (is_object($paymentMethodId)) {
            $paymentMethodId = $paymentMethodId->id ?? null;
        }

        $subscriptionData = [
            'customer' => $customerId,
            'items' => $subscriptionItems,
            'collection_method' => 'charge_automatically',
            'trial_end' => $trialEnd,
            'metadata' => [
                'invoice_id' => (string) $this->invoice->id,
                'checkout_token' => (string) $this->invoice->checkout_token,
                'source' => 'checkout_addons',
            ],
            'payment_settings' => [
                'save_default_payment_method' => 'on_subscription',
            ],
        ];

        if (is_string($paymentMethodId) && trim($paymentMethodId) !== '') {
            $subscriptionData['default_payment_method'] = trim($paymentMethodId);
        }

        $subscription = $stripe->subscriptions->create(
            $subscriptionData,
            [
                'idempotency_key' => 'checkout-addon-subscription-'.$this->invoice->id,
            ]
        );

        $this->invoice->update([
            'stripe_subscription_id' => (string) $subscription->id,
        ]);

        Log::info('Stripe recurring addon subscription created.', [
            'invoice_id' => $this->invoice->id,
            'subscription_id' => $subscription->id,
            'payment_intent_id' => $paymentIntent->id ?? null,
            'addon_item_count' => count($subscriptionItems),
            'trial_end' => $trialEnd,
        ]);
    }

    /**
     * A successful Stripe payment is a successful checkout even if the
     * Stripe fee/BalanceTransaction is temporarily unavailable. Accounting
     * synchronization is retried through Stripe webhook processing.
     */
    protected function applySuccessfulPaymentIntent(mixed $paymentIntent): void
    {
        $paymentIntentId = trim((string) ($paymentIntent->id ?? ''));

        if ($paymentIntentId === '') {
            throw new \RuntimeException('Stripe returned a successful payment without a PaymentIntent ID.');
        }

        if (($paymentIntent->status ?? null) !== 'succeeded') {
            throw new \RuntimeException('The Stripe PaymentIntent is not succeeded.');
        }

        $metadataInvoiceId = (string) ($paymentIntent->metadata->invoice_id ?? '');

        if ($metadataInvoiceId !== '' && $metadataInvoiceId !== (string) $this->invoice->id) {
            throw new \RuntimeException('The Stripe payment does not belong to this invoice.');
        }

        if (
            isset($paymentIntent->amount)
            && (int) $paymentIntent->amount !== $this->totalInCents
        ) {
            throw new \RuntimeException('The Stripe payment amount does not match this invoice.');
        }

        if (
            isset($paymentIntent->currency)
            && strtolower((string) $paymentIntent->currency) !== 'usd'
        ) {
            throw new \RuntimeException('The Stripe payment currency does not match this invoice.');
        }

        try {
            /*
             * IMPORTANT FIX:
             * Pass the local Invoice explicitly. Subscription PaymentIntents
             * can lack the invoice_id metadata that one-time checkouts carry.
             */
            app(StripeWebhookService::class)->recordSuccessfulPaymentIntent(
                $paymentIntentId,
                $this->invoice
            );
        } catch (Throwable $e) {
            /*
             * Never turn an already-successful Stripe payment into a Livewire
             * 500 because Stripe's accounting data is temporarily unavailable.
             * The webhook is authoritative and Stripe retries failed delivery.
             */
            Log::error('Stripe payment succeeded but local fee/accounting synchronization failed.', [
                'invoice_id' => $this->invoice->id,
                'payment_intent_id' => $paymentIntentId,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }

        $this->invoice->refresh();

        $this->paymentStatus = 'succeeded';
        $this->paymentIntentId = $paymentIntentId;
        $this->checkoutSuccessful = true;
        $this->checkoutError = null;

        session()->put($this->successfulCheckoutSessionKey(), true);

        $email = trim((string) (
            $this->billingEmail
            ?: ($paymentIntent->receipt_email ?? '')
        ));

        if ($email !== '') {
            $this->billingEmail = $email;
        }

        Log::info('Stripe checkout payment verified as successful.', [
            'invoice_id' => $this->invoice->id,
            'payment_intent_id' => $paymentIntentId,
            'invoice_status' => $this->invoice->status instanceof \BackedEnum
                ? $this->invoice->status->value
                : $this->invoice->status,
            'invoice_total_paid' => (int) $this->invoice->total_paid,
        ]);
    }

    protected function sendCompletionEmails(mixed $paymentIntent): void
    {
        if (($paymentIntent->status ?? null) !== 'succeeded') {
            return;
        }

        $paymentIntentId = trim((string) ($paymentIntent->id ?? ''));
        if ($paymentIntentId === '') {
            return;
        }

        $lock = Cache::lock(
            'checkout-email:'.$this->invoice->id.':'.$paymentIntentId,
            60
        );

        if (! $lock->get()) {
            return;
        }

        try {
            $customerEmail = trim((string) (
                $this->billingEmail
                ?: ($paymentIntent->receipt_email ?? '')
            ));

            if ($customerEmail !== '') {
                try {
                    Mail::to($customerEmail)->send(new PaymentReceived($this->invoice));
                } catch (Throwable $e) {
                    Log::error('Customer payment email failed.', [
                        'invoice_id' => $this->invoice->id,
                        'payment_intent_id' => $paymentIntentId,
                        'email' => $customerEmail,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            foreach ($this->getCheckoutAdminEmails() as $adminEmail) {
                if (
                    $customerEmail !== ''
                    && strtolower($adminEmail) === strtolower($customerEmail)
                ) {
                    continue;
                }

                try {
                    Mail::to($adminEmail)->send(new PaymentReceivedAdmin($this->invoice));
                } catch (Throwable $e) {
                    Log::error('Admin payment email failed.', [
                        'invoice_id' => $this->invoice->id,
                        'payment_intent_id' => $paymentIntentId,
                        'email' => $adminEmail,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } finally {
            $lock->release();
        }
    }

    protected function getCheckoutAdminEmails(): array
    {
        $emails = [];
        $configured = config('mail.checkout_admin_emails', []);

        if (is_string($configured)) {
            $configured = preg_split('/\s*,\s*/', $configured, -1, PREG_SPLIT_NO_EMPTY);
        }

        foreach ((array) $configured as $email) {
            $email = trim((string) $email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }

        try {
            if (Schema::hasColumn('users', 'role')) {
                $roleEmails = User::query()
                    ->whereIn('role', ['owner', 'admin'])
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->map(fn ($email) => trim((string) $email))
                    ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
                    ->all();

                $emails = array_merge($emails, $roleEmails);
            }
        } catch (Throwable $e) {
            Log::warning('Could not load checkout admin users by role column.', [
                'invoice_id' => $this->invoice->id ?? null,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            if (method_exists(User::class, 'roles')) {
                $roleEmails = User::query()
                    ->whereHas('roles', function ($query) {
                        $query->whereIn('name', ['owner', 'admin']);
                    })
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->map(fn ($email) => trim((string) $email))
                    ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
                    ->all();

                $emails = array_merge($emails, $roleEmails);
            }
        } catch (Throwable $e) {
            Log::warning('Could not load checkout admin users by roles relationship.', [
                'invoice_id' => $this->invoice->id ?? null,
                'message' => $e->getMessage(),
            ]);
        }

        return collect($emails)
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    public function validateBilling(): bool
    {
        $this->validate([
            'billingName' => ['required', 'string', 'max:255'],
            'billingEmail' => ['required', 'email', 'max:255'],
        ]);

        return true;
    }

    public function paymentCompleted(string $paymentIntentId): string
    {
        $paymentIntentId = trim($paymentIntentId);

        try {
            if (
                $paymentIntentId === ''
                || $this->paymentIntentId === null
                || $paymentIntentId !== $this->paymentIntentId
            ) {
                throw new \RuntimeException('The Stripe payment could not be verified.');
            }

            $stripe = $this->stripe();
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, []);

            $metadataInvoiceId = (string) ($paymentIntent->metadata->invoice_id ?? '');

            if ($metadataInvoiceId !== '' && $metadataInvoiceId !== (string) $this->invoice->id) {
                throw new \RuntimeException('The Stripe payment does not belong to this invoice.');
            }

            if (
                isset($paymentIntent->amount)
                && (int) $paymentIntent->amount !== $this->totalInCents
            ) {
                throw new \RuntimeException('The Stripe payment amount does not match this invoice.');
            }

            if (
                isset($paymentIntent->currency)
                && strtolower((string) $paymentIntent->currency) !== 'usd'
            ) {
                throw new \RuntimeException('The Stripe payment currency does not match this invoice.');
            }

            $this->paymentStatus = (string) ($paymentIntent->status ?? '');

            if (($paymentIntent->status ?? null) === 'succeeded') {
                $this->applySuccessfulPaymentIntent($paymentIntent);

                if ($this->invoice->payment_type === PaymentType::Subscription) {
                    /*
                     * The payment has already succeeded. Do not let a
                     * secondary recurring-addon provisioning failure change
                     * the checkout result into a 500/failed payment.
                     */
                    try {
                        $this->createRecurringAddonSubscription($paymentIntent);
                    } catch (Throwable $e) {
                        Log::error('Stripe recurring add-on provisioning failed after successful payment.', [
                            'invoice_id' => $this->invoice->id,
                            'payment_intent_id' => $paymentIntentId,
                            'message' => $e->getMessage(),
                            'exception' => get_class($e),
                        ]);
                    }
                }

                return 'succeeded';
            }

            $this->checkoutSuccessful = false;

            Log::info('Stripe checkout payment status verified.', [
                'invoice_id' => $this->invoice->id,
                'payment_intent_id' => $paymentIntent->id ?? null,
                'status' => $paymentIntent->status ?? null,
                'amount_in_cents' => $paymentIntent->amount ?? null,
            ]);

            return (string) ($paymentIntent->status ?? 'unknown');
        } catch (Throwable $e) {
            $this->checkoutSuccessful = false;
            $this->checkoutError = 'We could not verify your Stripe payment. Please try again.';

            Log::error('Stripe checkout payment verification failed.', [
                'invoice_id' => $this->invoice->id ?? null,
                'payment_intent_id' => $paymentIntentId,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return 'failed';
        }
    }

    public function render()
    {
        return view('livewire.checkout.stripe-checkout');
    }
}
