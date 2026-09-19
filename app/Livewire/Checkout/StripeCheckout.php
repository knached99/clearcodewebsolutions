<?php

namespace App\Livewire\Checkout;

use App\Mail\PaymentReceived;
use App\Mail\PaymentReceivedAdmin;
use App\Models\Invoice;
use App\Models\User;
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

    public int $totalInCents = 0;
    public string $formattedTotal = '0.00';

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

        $this->totalInCents = $this->calculateTotalInCents();

        $this->formattedTotal = number_format(
            $this->totalInCents / 100,
            2
        );

        /*
         * Stripe's minimum USD PaymentIntent amount is $0.50.
         */
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
         * If this invoice was already successfully completed in this
         * browser session, don't recreate the payment form.
         */
        if ($this->hasRememberedSuccessfulCheckout()) {
            $this->checkoutSuccessful = true;
            $this->paymentStatus = 'succeeded';

            return;
        }

        /*
         * Stripe redirect-based payment methods may return the customer
         * to this URL with the original PaymentIntent ID.
         */
        $returnedPaymentIntentId = trim(
            (string) request()->query('payment_intent', '')
        );

        if ($returnedPaymentIntentId !== '') {
            $this->restoreReturnedPaymentIntent(
                $returnedPaymentIntentId
            );

            return;
        }

        /*
         * Also check Stripe directly so that an invoice whose payment
         * already succeeded remains completed even if the customer
         * returns later without Stripe query parameters.
         */
        $successfulPaymentIntent = $this->findSuccessfulPaymentIntent();

        if ($successfulPaymentIntent !== null) {
            $this->applySuccessfulPaymentIntent(
                $successfulPaymentIntent
            );

            return;
        }

        $this->createPaymentIntent();
    }

    protected function itemAmountInCents($item): int
    {
        $amount =
            $item->total
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
                && !isset($item->total)
            ) {
                $amount *= (int) $item->quantity;
            }

            $total += $amount;
        }

        return $total;
    }

    public function formatItemAmount($item): string
    {
        return number_format(
            $this->itemAmountInCents($item) / 100,
            2
        );
    }

    protected function stripe(): StripeClient
    {
        $secret = trim(
            (string) config('services.stripe.secret')
        );

        if ($secret === '') {
            throw new \RuntimeException(
                'Stripe secret key is not configured.'
            );
        }

        return new StripeClient($secret);
    }

    protected function createPaymentIntent(): void
    {
        try {
            $stripe = $this->stripe();

            $email = trim($this->billingEmail);
            $name = trim($this->billingName);

            $customerId = null;

            /*
             * Re-use an existing Stripe customer when we have an email.
             */
            if ($email !== '') {
                $searchEmail = str_replace(
                    ['\\', "'"],
                    ['\\\\', "\\'"],
                    $email
                );

                $customers = $stripe->customers->search([
                    'query' => "email:'{$searchEmail}'",
                    'limit' => 1,
                ]);

                if (isset($customers->data[0])) {
                    $customer = $customers->data[0];

                    $update = [];

                    if (
                        $name !== ''
                        && $customer->name !== $name
                    ) {
                        $update['name'] = $name;
                    }

                    if ($customer->email !== $email) {
                        $update['email'] = $email;
                    }

                    if ($update !== []) {
                        $customer = $stripe->customers->update(
                            $customer->id,
                            $update
                        );
                    }
                } else {
                    $customerData = [
                        'email' => $email,
                    ];

                    if ($name !== '') {
                        $customerData['name'] = $name;
                    }

                    $customer = $stripe->customers->create(
                        $customerData
                    );
                }

                $customerId = $customer->id;
            } elseif ($name !== '') {
                $customer = $stripe->customers->create([
                    'name' => $name,
                ]);

                $customerId = $customer->id;
            }

            $paymentIntentData = [
                'amount' => $this->totalInCents,
                'currency' => 'usd',

                'automatic_payment_methods' => [
                    'enabled' => true,
                ],

                'description' =>
                    'Invoice #' . $this->invoice->id,

                'metadata' => [
                    'invoice_id' =>
                        (string) $this->invoice->id,

                    'checkout_token' =>
                        (string) $this->invoice->checkout_token,
                ],
            ];

            if ($customerId !== null) {
                $paymentIntentData['customer'] = $customerId;
            }

            if ($email !== '') {
                $paymentIntentData['receipt_email'] = $email;
            }

            $paymentIntent = $stripe->paymentIntents->create(
                $paymentIntentData
            );

            $this->paymentIntentId = $paymentIntent->id;

            $this->clientSecret = $paymentIntent->client_secret;

            $this->paymentStatus = $paymentIntent->status;

            $this->checkoutError = null;

            if (!$this->clientSecret) {
                throw new \RuntimeException(
                    'Stripe did not return a PaymentIntent client secret.'
                );
            }

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

                    'amount_display' =>
                        $this->formattedTotal,

                    'payment_method_types' =>
                        $paymentIntent->payment_method_types,
                ]
            );
        } catch (Throwable $e) {
            $this->clientSecret = null;
            $this->paymentIntentId = null;
            $this->paymentStatus = null;

            $this->checkoutError =
                'Stripe could not initialize this checkout. Please try again.';

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
                ]
            );
        }
    }

    protected function restoreReturnedPaymentIntent(
        string $paymentIntentId
    ): void {
        try {
            $stripe = $this->stripe();

            $paymentIntent =
                $stripe->paymentIntents->retrieve(
                    $paymentIntentId,
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
                $metadataInvoiceId
                !== (string) $this->invoice->id
            ) {
                throw new \RuntimeException(
                    'The returned Stripe payment does not belong to this invoice.'
                );
            }

            if (
                (int) $paymentIntent->amount
                !== $this->totalInCents
            ) {
                throw new \RuntimeException(
                    'The returned Stripe payment amount does not match this invoice.'
                );
            }

            if (
                strtolower(
                    (string) $paymentIntent->currency
                )
                !== 'usd'
            ) {
                throw new \RuntimeException(
                    'The returned Stripe payment currency does not match this invoice.'
                );
            }

            $this->paymentIntentId =
                $paymentIntent->id;

            $this->clientSecret =
                $paymentIntent->client_secret;

            $this->paymentStatus =
                $paymentIntent->status;

            $this->checkoutError = null;

            if (!$this->clientSecret) {
                throw new \RuntimeException(
                    'Stripe did not return a client secret for the returned payment.'
                );
            }

            if ((string) $paymentIntent->status === 'succeeded') {
                $this->applySuccessfulPaymentIntent(
                    $paymentIntent
                );
            } else {
                $this->checkoutSuccessful = false;
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
                        $this->invoice->id ?? null,

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

    protected function hasRememberedSuccessfulCheckout(): bool
    {
        return (bool) session()->get(
            $this->successfulCheckoutSessionKey(),
            false
        );
    }

    protected function successfulCheckoutSessionKey(): string
    {
        return 'checkout.success.' . $this->invoice->id;
    }

    protected function findSuccessfulPaymentIntent(): mixed
    {
        try {
            $invoiceId = str_replace(
                "'",
                "\\'",
                (string) $this->invoice->id
            );

            $results = $this->stripe()->paymentIntents->search([
                'query' =>
                    "metadata['invoice_id']:'{$invoiceId}' AND status:'succeeded'",

                'limit' => 1,
            ]);

            return $results->data[0] ?? null;
        } catch (Throwable $e) {
            Log::warning(
                'Could not search Stripe for a previously successful checkout.',
                [
                    'invoice_id' =>
                        $this->invoice->id ?? null,

                    'message' =>
                        $e->getMessage(),
                ]
            );

            return null;
        }
    }

    protected function applySuccessfulPaymentIntent(
        mixed $paymentIntent
    ): void {
        $this->paymentStatus = 'succeeded';

        $this->paymentIntentId =
            (string) $paymentIntent->id;

        $this->checkoutSuccessful = true;

        $this->checkoutError = null;

        session()->put(
            $this->successfulCheckoutSessionKey(),
            true
        );

        $email = trim(
            (string) (
                $this->billingEmail
                ?: ($paymentIntent->receipt_email ?? '')
            )
        );

        if ($email !== '') {
            $this->billingEmail = $email;
        }

        /*
         * IMPORTANT:
         *
         * This remains here for compatibility with your existing
         * checkout flow.
         *
         * For maximum reliability, move payment processing/email
         * handling to a Stripe webhook as described below.
         */
        $this->sendCompletionEmails($paymentIntent);

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

    protected function sendCompletionEmails(
        mixed $paymentIntent
    ): void {
        if (
            (string) ($paymentIntent->status ?? '')
            !== 'succeeded'
        ) {
            return;
        }

        $paymentIntentId =
            (string) $paymentIntent->id;

        /*
         * Prevent duplicate email attempts caused by multiple
         * Livewire requests hitting the same successful checkout.
         */
        $lock = Cache::lock(
            'checkout-email:' .
            $this->invoice->id .
            ':' .
            $paymentIntentId,
            60
        );

        if (!$lock->get()) {
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
            $customerEmail = trim(
                (string) (
                    $this->billingEmail
                    ?: ($paymentIntent->receipt_email ?? '')
                )
            );

            /*
             * CUSTOMER EMAIL
             */
            if ($customerEmail !== '') {
                try {
                    Mail::to($customerEmail)
                        ->send(
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
            } else {
                Log::warning(
                    'Customer payment email skipped because no email address was available.',
                    [
                        'invoice_id' =>
                            $this->invoice->id,

                        'payment_intent_id' =>
                            $paymentIntentId,
                    ]
                );
            }

            /*
             * ADMIN EMAILS
             *
             * Each admin is handled independently so one failed
             * recipient does not prevent the others from receiving.
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
                    && strtolower($adminEmail)
                        === strtolower($customerEmail)
                ) {
                    continue;
                }

                try {
                    Mail::to($adminEmail)
                        ->send(
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
                        $this->invoice->id ?? null,

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

    protected function getCheckoutAdminEmails(): array
    {
        $emails = [];

        try {
            if (Schema::hasColumn('users', 'role')) {
                $emails = User::query()
                    ->whereIn(
                        'role',
                        [
                            'owner',
                            'admin',
                            'member',
                        ]
                    )
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->map(
                        fn ($email) =>
                            trim((string) $email)
                    )
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            } elseif (
                method_exists(
                    User::class,
                    'roles'
                )
            ) {
                $emails = User::query()
                    ->whereHas(
                        'roles',
                        function ($query) {
                            $query->whereIn(
                                'name',
                                [
                                    'owner',
                                    'admin',
                                    'member',
                                ]
                            );
                        }
                    )
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->map(
                        fn ($email) =>
                            trim((string) $email)
                    )
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }
        } catch (Throwable $e) {
            Log::warning(
                'Could not determine checkout admin email recipients from users.',
                [
                    'invoice_id' =>
                        $this->invoice->id ?? null,

                    'message' =>
                        $e->getMessage(),
                ]
            );
        }

        /*
         * Optional fallback:
         *
         * config/mail.php:
         *
         * 'checkout_admin_emails' => env(
         *     'CHECKOUT_ADMIN_EMAILS',
         *     ''
         * ),
         *
         * .env:
         *
         * CHECKOUT_ADMIN_EMAILS=admin@example.com
         */
        $configured =
            config(
                'mail.checkout_admin_emails',
                []
            );

        if (is_string($configured)) {
            $configured = preg_split(
                '/\s*,\s*/',
                $configured,
                -1,
                PREG_SPLIT_NO_EMPTY
            );
        }

        return collect(
            array_merge(
                $emails,
                (array) $configured
            )
        )
            ->map(
                fn ($email) =>
                    trim((string) $email)
            )
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

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

    public function paymentCompleted(
        string $paymentIntentId
    ): string {
        $paymentIntentId =
            trim($paymentIntentId);

        /*
         * Never trust the browser to tell us which PaymentIntent
         * was paid.
         */
        if (
            $paymentIntentId === ''
            || $paymentIntentId !== $this->paymentIntentId
        ) {
            throw new \RuntimeException(
                'The Stripe payment could not be verified.'
            );
        }

        $stripe = $this->stripe();

        /*
         * Retrieve the PaymentIntent directly from Stripe.
         */
        $paymentIntent =
            $stripe->paymentIntents->retrieve(
                $paymentIntentId,
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
            $metadataInvoiceId
            !== (string) $this->invoice->id
        ) {
            throw new \RuntimeException(
                'The Stripe payment does not belong to this invoice.'
            );
        }

        if (
            (int) $paymentIntent->amount
            !== $this->totalInCents
        ) {
            throw new \RuntimeException(
                'The Stripe payment amount does not match this invoice.'
            );
        }

        if (
            strtolower(
                (string) $paymentIntent->currency
            )
            !== 'usd'
        ) {
            throw new \RuntimeException(
                'The Stripe payment currency does not match this invoice.'
            );
        }

        $this->paymentStatus =
            $paymentIntent->status;

        if (
            (string) $paymentIntent->status
            === 'succeeded'
        ) {
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

    public function render()
    {
        return view(
            'livewire.checkout.stripe-checkout'
        );
    }
}