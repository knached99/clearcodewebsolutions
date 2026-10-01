<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
use App\Mail\PaymentReceivedAdmin;
use App\Mail\PaymentReceiptMail;
use App\Mail\SubscriptionRenewalDecisionMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\CustomerPaymentLocation;
use App\Services\CustomerLocationService; 
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Throwable;

class StripeWebhookService
{
    /**
     * Stripe can make the PaymentIntent/Charge visible before the related
     * BalanceTransaction is immediately retrievable. These delays are used
     * for short, synchronous retries before the webhook is allowed to fail
     * and Stripe can retry the event later.
     */
    protected const LOOKUP_RETRY_DELAYS = [0, 1, 2, 4];

    protected StripeClient $stripe;

    public function __construct(CustomerLocationService $customerLocationService)
    {
        $secret = trim(
            (string) config('services.stripe.secret', '')
        );

        if ($secret === '') {
            throw new \RuntimeException(
                'Stripe secret key is not configured.'
            );
        }

        $this->stripe = new StripeClient($secret);

        $this->customerLocationService = $customerLocationService;
    }

    /*
    |--------------------------------------------------------------------------
    | Main Webhook Handler
    |--------------------------------------------------------------------------
    */

    /**
     * Parse, verify, and dispatch a Stripe webhook.
     *
     * @throws \UnexpectedValueException
     * @throws SignatureVerificationException
     * @throws Throwable
     */
    public function handle(
        string $payload,
        ?string $signatureHeader = null
    ): void {
        $startedAt = microtime(true);

        $event = $this->constructEvent(
            $payload,
            $signatureHeader
        );

        if (!is_object($event) || !isset($event->type)) {
            throw new \UnexpectedValueException(
                'Invalid Stripe event payload.'
            );
        }

        $eventType = (string) $event->type;
        $eventId = isset($event->id)
            ? (string) $event->id
            : null;
        $eventPayload = $event->data->object ?? null;

        if (!is_object($eventPayload)) {
            Log::warning(
                'Stripe webhook missing event payload.',
                [
                    'event_type' => $eventType,
                    'event_id' => $eventId,
                ]
            );

            return;
        }

        Log::info(
            'Stripe webhook received.',
            [
                'event_type' => $eventType,
                'event_id' => $eventId,
            ]
        );

        switch ($eventType) {
            case 'invoice.upcoming':
                $this->handleInvoiceUpcoming($eventPayload);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($eventPayload);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($eventPayload);
                break;

            case 'charge.succeeded':
                $this->handleChargeSucceeded($eventPayload);
                break;

            case 'charge.failed':
                $this->handleChargeFailed($eventPayload);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($eventPayload);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($eventPayload);
                break;

            default:
                Log::debug(
                    'Stripe webhook event ignored.',
                    [
                        'event_type' => $eventType,
                        'event_id' => $eventId,
                    ]
                );

                break;
        }

        Log::info(
            'Stripe webhook completed.',
            [
                'event_type' => $eventType,
                'event_id' => $eventId,
                'duration_ms' => round(
                    (microtime(true) - $startedAt) * 1000,
                    2
                ),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Event Construction
    |--------------------------------------------------------------------------
    */

    protected function constructEvent(
        string $payload,
        ?string $signatureHeader
    ): object {
        $endpointSecret = trim(
            (string) config(
                'services.stripe.webhook_secret',
                ''
            )
        );

        if ($endpointSecret !== '') {
            if (!is_string($signatureHeader) || trim($signatureHeader) === '') {
                throw new SignatureVerificationException(
                    'Missing Stripe webhook signature header.'
                );
            }

            return Webhook::constructEvent(
                $payload,
                $signatureHeader,
                $endpointSecret
            );
        }

        if (app()->environment('production')) {
            throw new \RuntimeException(
                'Stripe webhook secret is not configured in production.'
            );
        }

        $event = json_decode($payload);

        if (
            !is_object($event)
            || json_last_error() !== JSON_ERROR_NONE
        ) {
            throw new \UnexpectedValueException(
                'Unable to decode Stripe webhook payload.'
            );
        }

        return $event;
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Upcoming
    |--------------------------------------------------------------------------
    */

    protected function handleInvoiceUpcoming(
        object $invoiceObject
    ): void {
        $customerEmail = $this->resolveCustomerEmail(
            $invoiceObject
        );

        if ($customerEmail === null) {
            Log::warning(
                'Stripe invoice.upcoming has no resolvable customer email.',
                [
                    'stripe_invoice_id' => $invoiceObject->id ?? null,
                    'subscription_id' => $this->stripeObjectId(
                        $invoiceObject->subscription ?? null
                    ),
                ]
            );

            return;
        }

        $localInvoice = $this->resolveInvoice(
            $invoiceObject
        );

        if ($localInvoice) {
            $this->syncInvoiceStripeReferences(
                $localInvoice,
                $invoiceObject
            );
        }

        $isRecurringInvoice =
            $localInvoice
            && (
                $localInvoice->payment_type === PaymentType::Subscription
                || $localInvoice->payment_type === PaymentType::Subscription->value
                || $localInvoice->hasRecurringBilling()
            );

        $hasCompletedTwelveMonthMinimum =
            $localInvoice
            && (int) ($localInvoice->subscription_months ?? 0) >= 12;

        if (
            $localInvoice
            && $isRecurringInvoice
            && $hasCompletedTwelveMonthMinimum
        ) {
            try {
                Mail::to($customerEmail)->send(
                    new SubscriptionRenewalDecisionMail(
                        invoice: $localInvoice,
                        renewalDate: now()
                            ->addDays(10)
                            ->format('F j, Y'),
                        monthlyRecurringAmountInCents:
                            $localInvoice->monthlyRecurringAmountInCents(),
                        addonCount:
                            $localInvoice
                                ->items()
                                ->where('is_subscription', true)
                                ->whereNotNull('stripe_price_id')
                                ->count(),
                    )
                );
            } catch (Throwable $e) {
                Log::error(
                    '12-month subscription renewal decision email failed.',
                    [
                        'customer_email' => $customerEmail,
                        'invoice_id' => $localInvoice->id,
                        'stripe_invoice_id' => $invoiceObject->id ?? null,
                        'message' => $e->getMessage(),
                    ]
                );

                return;
            }

            Log::info(
                '12-month subscription renewal decision email sent.',
                [
                    'customer_email' => $customerEmail,
                    'invoice_id' => $localInvoice->id,
                    'stripe_invoice_id' => $invoiceObject->id ?? null,
                    'subscription_id' => $this->stripeObjectId(
                        $invoiceObject->subscription ?? null
                    ),
                ]
            );

            return;
        }

        try {
            Mail::raw(
                "Your subscription renewal is coming up soon. "
                . "Please make sure your card on file is ready "
                . "for the upcoming billing date.\n\n"
                . "Thank you!",
                function ($message) use ($customerEmail): void {
                    $message
                        ->to($customerEmail)
                        ->subject('Upcoming subscription renewal');
                }
            );
        } catch (Throwable $e) {
            Log::error(
                'Upcoming renewal notification failed.',
                [
                    'customer_email' => $customerEmail,
                    'invoice_id' => $localInvoice?->id,
                    'stripe_invoice_id' => $invoiceObject->id ?? null,
                    'message' => $e->getMessage(),
                ]
            );

            return;
        }

        Log::info(
            'Upcoming renewal notification sent.',
            [
                'customer_email' => $customerEmail,
                'invoice_id' => $localInvoice?->id,
                'stripe_invoice_id' => $invoiceObject->id ?? null,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Payment Succeeded
    |--------------------------------------------------------------------------
    */

    protected function handleInvoicePaymentSucceeded(
        object $invoiceObject
    ): void {
        Log::info(
            'Stripe invoice.payment_succeeded received.',
            [
                'stripe_invoice_id' => $invoiceObject->id ?? null,
                'customer' => $this->stripeObjectId(
                    $invoiceObject->customer ?? null
                ),
                'customer_email' => $invoiceObject->customer_email ?? null,
                'subscription' => $this->stripeObjectId(
                    $invoiceObject->subscription ?? null
                ),
                'payment_intent' => $this->stripeObjectId(
                    $invoiceObject->payment_intent ?? null
                ),
                'metadata' => isset($invoiceObject->metadata)
                    ? (array) $invoiceObject->metadata
                    : [],
            ]
        );

        $invoice = $this->resolveInvoice(
            $invoiceObject
        );

        if ($invoice) {
            $this->syncInvoiceStripeReferences(
                $invoice,
                $invoiceObject
            );
        }

        $paymentIntentId = $this->stripeObjectId(
            $invoiceObject->payment_intent ?? null
        );

        if ($paymentIntentId) {
            $invoiceChargeHint =
                is_object($invoiceObject->charge ?? null)
                    ? $invoiceObject->charge
                    : null;

            $transaction = $this->recordSuccessfulPaymentIntent(
                $paymentIntentId,
                $invoice,
                $invoiceObject,
                $invoiceChargeHint
            );

            if (!$invoice && $transaction) {
                $invoice = $transaction->invoice;
            }

            return;
        }

        if ($invoice) {
            Log::warning(
                'Stripe invoice.payment_succeeded has no PaymentIntent. '
                . 'No transaction will be fabricated and invoice payment '
                . 'totals will remain transaction-derived.',
                [
                    'invoice_id' => $invoice->id,
                    'stripe_invoice_id' => $invoiceObject->id ?? null,
                    'stripe_charge_id' => $this->stripeObjectId(
                        $invoiceObject->charge ?? null
                    ),
                ]
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Payment Failed
    |--------------------------------------------------------------------------
    */

    protected function handleInvoicePaymentFailed(
        object $invoiceObject
    ): void {
        $invoice = $this->resolveInvoice(
            $invoiceObject
        );

        if ($invoice) {
            $this->syncInvoiceStripeReferences(
                $invoice,
                $invoiceObject
            );
        }

        $paymentIntentId = $this->stripeObjectId(
            $invoiceObject->payment_intent ?? null
        );

        $errorMessage = $this->resolveStripeFailureMessage(
            $invoiceObject
        );

        if ($invoice) {
            $this->recordFailedTransaction(
                invoice: $invoice,
                paymentIntentId: $paymentIntentId,
                errorMessage: $errorMessage,
                stripeObject: $invoiceObject
            );

            $invoice->refresh();

            if ((int) $invoice->total_paid <= 0) {
                $invoice->status = InvoiceStatus::Failed;
                $invoice->save();
            }
        }

        $this->sendFailureNotifications(
            $invoice,
            $invoiceObject,
            $errorMessage
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Charge Succeeded
    |--------------------------------------------------------------------------
    | This is an additional reconciliation path. If Stripe has already
    | produced the successful Charge, it gives us a direct Charge hint while
    | we resolve the same authoritative PaymentIntent ledger record.
    |--------------------------------------------------------------------------
    */

    protected function handleChargeSucceeded(
        object $charge
    ): void {
        $paymentIntentId = $this->stripeObjectId(
            $charge->payment_intent ?? null
        );

        if (!$paymentIntentId) {
            Log::info(
                'Stripe charge.succeeded has no PaymentIntent; nothing to reconcile.',
                [
                    'charge_id' => $charge->id ?? null,
                ]
            );

            return;
        }

        try {
            $paymentIntent = $this->stripe
                ->paymentIntents
                ->retrieve(
                    $paymentIntentId,
                    [
                        'expand' => ['latest_charge'],
                    ]
                );

            $invoice = $this->resolveInvoiceFromPaymentIntent(
                $paymentIntent
            );

            if (!$invoice) {
                $invoice = $this->resolveInvoice($charge);
            }

            if (!$invoice) {
                Log::warning(
                    'Successful Stripe Charge could not be linked to a local invoice.',
                    [
                        'payment_intent_id' => $paymentIntentId,
                        'charge_id' => $charge->id ?? null,
                    ]
                );

                return;
            }

            $this->recordSuccessfulPaymentIntent(
                $paymentIntentId,
                $invoice,
                null,
                $charge
            );
        } catch (Throwable $e) {
            Log::error(
                'Stripe charge.succeeded reconciliation failed.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'charge_id' => $charge->id ?? null,
                    'message' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Charge Failed
    |--------------------------------------------------------------------------
    */

    protected function handleChargeFailed(
        object $charge
    ): void {
        $paymentIntentId = $this->stripeObjectId(
            $charge->payment_intent ?? null
        );

        $invoice = null;

        if ($paymentIntentId) {
            try {
                $paymentIntent = $this->stripe
                    ->paymentIntents
                    ->retrieve($paymentIntentId);

                $invoice = $this->resolveInvoiceFromPaymentIntent(
                    $paymentIntent
                );
            } catch (Throwable $e) {
                Log::warning(
                    'Could not resolve invoice from failed PaymentIntent.',
                    [
                        'payment_intent_id' => $paymentIntentId,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }

        if (!$invoice) {
            $invoice = $this->resolveInvoice($charge);
        }

        $errorMessage = (string) (
            data_get($charge, 'failure_message')
            ?? 'Stripe charge failed.'
        );

        if ($invoice) {
            $this->recordFailedTransaction(
                invoice: $invoice,
                paymentIntentId: $paymentIntentId,
                errorMessage: $errorMessage,
                stripeObject: $charge
            );

            $invoice->refresh();

            if ((int) $invoice->total_paid <= 0) {
                $invoice->status = InvoiceStatus::Failed;
                $invoice->save();
            }
        }

        $this->sendFailureNotifications(
            $invoice,
            $charge,
            $errorMessage
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PaymentIntent Succeeded
    |--------------------------------------------------------------------------
    */

    protected function handlePaymentIntentSucceeded(
        object $paymentIntent
    ): void {
        $paymentIntentId = $this->stripeObjectId(
            $paymentIntent->id ?? null
        );

        if (!$paymentIntentId) {
            Log::warning(
                'Stripe PaymentIntent succeeded without an ID.'
            );

            return;
        }

        $invoice = $this->resolveInvoiceFromPaymentIntent(
            $paymentIntent
        );

        if (!$invoice) {
            Log::warning(
                'Successful PaymentIntent could not be linked to a local invoice.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'invoice_id' => data_get(
                        $paymentIntent,
                        'metadata.invoice_id'
                    ),
                ]
            );

            return;
        }

        $this->recordSuccessfulPaymentIntent(
            $paymentIntentId,
            $invoice
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PaymentIntent Failed
    |--------------------------------------------------------------------------
    */

    protected function handlePaymentIntentFailed(
        object $paymentIntent
    ): void {
        $paymentIntentId = $this->stripeObjectId(
            $paymentIntent->id ?? null
        );

        $invoice = $this->resolveInvoiceFromPaymentIntent(
            $paymentIntent
        );

        $errorMessage = $this->resolveStripeFailureMessage(
            $paymentIntent
        );

        if ($invoice) {
            $this->recordFailedTransaction(
                invoice: $invoice,
                paymentIntentId: $paymentIntentId,
                errorMessage: $errorMessage,
                stripeObject: $paymentIntent
            );

            $invoice->refresh();

            if ((int) $invoice->total_paid <= 0) {
                $invoice->status = InvoiceStatus::Failed;
                $invoice->save();
            }
        }

        $this->sendFailureNotifications(
            $invoice,
            $paymentIntent,
            $errorMessage
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Successful PaymentIntent -> Transaction
    |--------------------------------------------------------------------------
    */

    public function recordSuccessfulPaymentIntent(
        string $paymentIntentId,
        ?Invoice $invoice = null,
        ?object $invoiceObject = null,
        mixed $chargeHint = null
    ): ?Transaction {
        $paymentIntentId = trim($paymentIntentId);

        if ($paymentIntentId === '') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Retrieve authoritative PaymentIntent
        |--------------------------------------------------------------------------
        */

        $paymentIntent = $this->stripe
            ->paymentIntents
            ->retrieve(
                $paymentIntentId,
                [
                    'expand' => ['latest_charge'],
                ]
            );

        if (($paymentIntent->status ?? null) !== 'succeeded') {
            Log::warning(
                'PaymentIntent was not succeeded during transaction sync.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'status' => $paymentIntent->status ?? null,
                ]
            );

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve invoice from PaymentIntent metadata first
        |--------------------------------------------------------------------------
        */

        $resolvedInvoice = $this->resolveInvoiceFromPaymentIntent(
            $paymentIntent
        );

        if ($resolvedInvoice) {
            $invoice = $resolvedInvoice;
        }

        /*
        |--------------------------------------------------------------------------
        | Fall back to invoice webhook object
        |--------------------------------------------------------------------------
        */

        if (!$invoice && $invoiceObject) {
            $invoice = $this->resolveInvoice($invoiceObject);
        }

        if (!$invoice) {
            Log::error(
                'Successful PaymentIntent cannot be linked to a local invoice.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'metadata' => isset($paymentIntent->metadata)
                        ? (array) $paymentIntent->metadata
                        : [],
                ]
            );

            return null;
        }

        if ($invoiceObject) {
            $this->syncInvoiceStripeReferences(
                $invoice,
                $invoiceObject
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Charge
        |--------------------------------------------------------------------------
        */

        $charge = $this->resolvePaymentIntentCharge(
            $paymentIntent,
            $chargeHint
        );

        if (!is_object($charge) || empty($charge->id)) {
            Log::error(
                'Successful PaymentIntent has no resolvable Stripe Charge.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'invoice_id' => $invoice->id,
                ]
            );

            throw new \RuntimeException(
                "Unable to resolve Stripe Charge for PaymentIntent {$paymentIntentId}."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        |
        | Charge amount is the authoritative amount actually charged.
        | PaymentIntent amount_received is the next fallback.
        |
        */

        $chargeAmount = isset($charge->amount)
            ? (int) $charge->amount
            : 0;

        $paymentIntentAmountReceived = isset(
            $paymentIntent->amount_received
        )
            ? (int) $paymentIntent->amount_received
            : 0;

        $paymentIntentAmount = isset($paymentIntent->amount)
            ? (int) $paymentIntent->amount
            : 0;

        $amount = max(
            $chargeAmount > 0
                ? $chargeAmount
                : (
                    $paymentIntentAmountReceived > 0
                        ? $paymentIntentAmountReceived
                        : $paymentIntentAmount
                ),
            0
        );

        if ($amount <= 0) {
            Log::warning(
                'Successful Stripe PaymentIntent has no positive charged amount.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'charge_id' => $charge->id ?? null,
                    'payment_intent_amount' => $paymentIntentAmount,
                    'payment_intent_amount_received' => $paymentIntentAmountReceived,
                    'charge_amount' => $chargeAmount,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Method
        |--------------------------------------------------------------------------
        */

        $paymentMethodData = $this->resolvePaymentMethodData(
            $paymentIntent,
            $charge
        );

        /*
        |--------------------------------------------------------------------------
        | Stripe BalanceTransaction / Fee
        |--------------------------------------------------------------------------
        |
        | NEVER manufacture processing_fee = 0 when Stripe has not supplied
        | the BalanceTransaction. We retry retrieval and then let the webhook
        | fail so Stripe can retry the event later if the data is still missing.
        |
        */

        $balanceTransaction = $this->resolveBalanceTransaction(
            $charge
        );

        if (
            !is_object($balanceTransaction)
            || empty($balanceTransaction->id)
        ) {
            Log::error(
                'Stripe BalanceTransaction could not be resolved.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'charge_id' => $charge->id ?? null,
                    'invoice_id' => $invoice->id,
                ]
            );

            throw new \RuntimeException(
                "Unable to resolve Stripe BalanceTransaction for PaymentIntent {$paymentIntentId}."
            );
        }

        $balanceTransactionId = $this->stripeObjectId(
            $balanceTransaction
        );

        if (
            !isset($balanceTransaction->fee)
            || !isset($balanceTransaction->net)
        ) {
            Log::error(
                'Stripe BalanceTransaction is missing fee or net.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'charge_id' => $charge->id ?? null,
                    'balance_transaction_id' => $balanceTransactionId,
                    'invoice_id' => $invoice->id,
                ]
            );

            throw new \RuntimeException(
                "Stripe BalanceTransaction {$balanceTransactionId} is missing fee or net."
            );
        }

        $processingFee = (int) $balanceTransaction->fee;
        $netAmount = (int) $balanceTransaction->net;

        if ($processingFee < 0) {
            Log::error(
                'Stripe BalanceTransaction returned an invalid negative fee.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'charge_id' => $charge->id ?? null,
                    'balance_transaction_id' => $balanceTransactionId,
                    'fee' => $processingFee,
                ]
            );

            throw new \RuntimeException(
                "Stripe BalanceTransaction {$balanceTransactionId} contains an invalid fee."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Paid At
        |--------------------------------------------------------------------------
        */

        $paidTimestamp = isset($charge->created)
            ? (int) $charge->created
            : (
                isset($paymentIntent->created)
                    ? (int) $paymentIntent->created
                    : now()->timestamp
            );

        $paidAt = Carbon::createFromTimestamp(
            $paidTimestamp,
            config('app.timezone')
        );

        /*
        |--------------------------------------------------------------------------
        | Store / update transaction under a row lock
        |--------------------------------------------------------------------------
        */

       [$transaction, $wasAlreadySucceeded] = DB::transaction(
    function () use (
        $invoice,
        $paymentIntentId,
        $paymentMethodData,
        $amount,
        $processingFee,
        $netAmount,
        $charge,
        $balanceTransactionId,
        $paidAt
    ): array {
        $existingTransaction = Transaction::query()
            ->where(
                'stripe_payment_intent_id',
                $paymentIntentId
            )
            ->lockForUpdate()
            ->first();

        $wasAlreadySucceeded =
            $existingTransaction
            && $this->isTransactionSucceeded(
                $existingTransaction
            );

        $values = [
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_charge_id' => $charge->id ?? null,
            'stripe_balance_transaction_id' => $balanceTransactionId,
            'stripe_payment_method_id' => $paymentMethodData['payment_method_id'],
            'amount' => $amount,
            'processing_fee' => $processingFee,
            'net_amount' => $netAmount,
            'status' => TransactionStatus::Succeeded,
            'paid_at' => $paidAt,
            'payment_method_type' => $paymentMethodData['payment_method_type'],
            'brand' => $paymentMethodData['brand'],
            'last4' => $paymentMethodData['last4'],
            'error_message' => null,
        ];

        if ($existingTransaction) {
            $existingTransaction->update($values);

            $transaction = $existingTransaction->fresh([
                'invoice',
                'customer',
            ]);
        } else {
            $transaction = Transaction::create($values);
        }

        return [
            $transaction,
            (bool) $wasAlreadySucceeded,
        ];
    }
);


/*
|--------------------------------------------------------------------------
| Capture Customer Payment Location
|--------------------------------------------------------------------------
*/
 $checkoutIp = data_get($paymentIntent, 'metadata.checkout_ip');

 if(is_string($checkoutIp) && trim($checkoutIp) !== '') {
    $this->capturePaymentLocation(
        customer: $invoice->customer, 
        transaction: $transaction, 
        ipAddress: trim($checkoutIp),
    );
 }


/*
|--------------------------------------------------------------------------
| Sync invoice totals from successful transaction ledger
|--------------------------------------------------------------------------
*/

$invoice->refresh();
$invoice->syncPaymentTotals();

        $invoice->load([
            'customer',
            'items',
            'transactions',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        if (!$wasAlreadySucceeded) {
            $customerEmail = $invoice->customer?->email;

            if (is_string($customerEmail) && trim($customerEmail) !== '') {
                try {
                    Mail::to(trim($customerEmail))->send(
                        new PaymentReceiptMail($invoice)
                    );
                } catch (Throwable $e) {
                    Log::error(
                        'Customer payment receipt failed.',
                        [
                            'invoice_id' => $invoice->id,
                            'payment_intent_id' => $paymentIntentId,
                            'email' => $customerEmail,
                            'message' => $e->getMessage(),
                        ]
                    );
                }
            }

            $this->sendAdminSuccessNotification(
                $invoice
            );
        }

        Log::info(
            'Stripe PaymentIntent synchronized to transaction ledger.',
            [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'payment_intent_id' => $paymentIntentId,
                'charge_id' => $charge->id ?? null,
                'balance_transaction_id' => $balanceTransactionId,
                'payment_method_id' => $paymentMethodData['payment_method_id'],
                'amount' => $amount,
                'processing_fee' => $processingFee,
                'net_amount' => $netAmount,
                'paid_at' => $paidAt->toDateTimeString(),
                'invoice_total_amount' => $invoice->total_amount,
                'invoice_total_paid' => $invoice->total_paid,
                'invoice_status' => $invoice->status instanceof InvoiceStatus
                    ? $invoice->status->value
                    : $invoice->status,
            ]
        );

        return $transaction;
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve PaymentIntent Charge
    |--------------------------------------------------------------------------
    */

    protected function resolvePaymentIntentCharge(
        object $paymentIntent,
        mixed $chargeHint = null
    ): ?object {
        $paymentIntentId = $this->stripeObjectId(
            $paymentIntent->id ?? null
        );

        if (!$paymentIntentId) {
            Log::error(
                'Cannot resolve Stripe Charge because PaymentIntent has no ID.'
            );

            return null;
        }

        $candidateChargeIds = [];
        $candidateChargeIds = $this->appendChargeId(
            $candidateChargeIds,
            $chargeHint
        );
        $candidateChargeIds = $this->appendChargeId(
            $candidateChargeIds,
            $paymentIntent->latest_charge ?? null
        );

        foreach ($candidateChargeIds as $chargeId) {
            $charge = $this->retrieveChargeWithBalanceTransaction(
                $chargeId,
                $paymentIntentId
            );

            if ($charge) {
                return $charge;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Re-fetch the PaymentIntent and retry while Stripe propagates the Charge
        |--------------------------------------------------------------------------
        */

        foreach (self::LOOKUP_RETRY_DELAYS as $delay) {
            if ($delay > 0) {
                sleep($delay);
            }

            try {
                $freshPaymentIntent = $this->stripe
                    ->paymentIntents
                    ->retrieve(
                        $paymentIntentId,
                        [
                            'expand' => ['latest_charge'],
                        ]
                    );

                $latestCharge = $freshPaymentIntent->latest_charge ?? null;

                $candidateChargeIds = $this->appendChargeId(
                    [],
                    $latestCharge
                );

                foreach ($candidateChargeIds as $chargeId) {
                    $charge = $this->retrieveChargeWithBalanceTransaction(
                        $chargeId,
                        $paymentIntentId
                    );

                    if ($charge) {
                        return $charge;
                    }
                }

                /*
                |------------------------------------------------------------------
                | Fallback: list Charges by PaymentIntent
                |------------------------------------------------------------------
                */

                $charges = $this->stripe
                    ->charges
                    ->all([
                        'payment_intent' => $paymentIntentId,
                        'limit' => 10,
                    ]);

                foreach ($charges->data ?? [] as $candidateCharge) {
                    if (
                        !is_object($candidateCharge)
                        || empty($candidateCharge->id)
                    ) {
                        continue;
                    }

                    if (
                        isset($candidateCharge->status)
                        && $candidateCharge->status !== 'succeeded'
                    ) {
                        continue;
                    }

                    $charge = $this->retrieveChargeWithBalanceTransaction(
                        $candidateCharge->id,
                        $paymentIntentId
                    );

                    if ($charge) {
                        return $charge;
                    }
                }
            } catch (Throwable $e) {
                Log::warning(
                    'Stripe Charge lookup attempt failed; will retry.',
                    [
                        'payment_intent_id' => $paymentIntentId,
                        'delay_seconds' => $delay,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }

        Log::error(
            'Unable to resolve any Stripe Charge for PaymentIntent.',
            [
                'payment_intent_id' => $paymentIntentId,
                'latest_charge_type' => get_debug_type(
                    $paymentIntent->latest_charge ?? null
                ),
            ]
        );

        return null;
    }

    protected function appendChargeId(
        array $chargeIds,
        mixed $value
    ): array {
        $chargeId = $this->stripeObjectId($value);

        if ($chargeId && str_starts_with($chargeId, 'ch_')) {
            if (!in_array($chargeId, $chargeIds, true)) {
                $chargeIds[] = $chargeId;
            }
        }

        return $chargeIds;
    }

    protected function retrieveChargeWithBalanceTransaction(
        string $chargeId,
        string $paymentIntentId
    ): ?object {
        try {
            $charge = $this->stripe
                ->charges
                ->retrieve(
                    $chargeId,
                    [
                        'expand' => ['balance_transaction'],
                    ]
                );

            if (
                !is_object($charge)
                || empty($charge->id)
            ) {
                return null;
            }

            if (
                isset($charge->status)
                && $charge->status !== 'succeeded'
            ) {
                return null;
            }

            Log::info(
                'Stripe Charge resolved.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'charge_id' => $charge->id,
                    'balance_transaction_id' => $this->stripeObjectId(
                        $charge->balance_transaction ?? null
                    ),
                ]
            );

            return $charge;
        } catch (Throwable $e) {
            Log::warning(
                'Could not retrieve Stripe Charge.',
                [
                    'payment_intent_id' => $paymentIntentId,
                    'charge_id' => $chargeId,
                    'message' => $e->getMessage(),
                ]
            );

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Balance Transaction
    |--------------------------------------------------------------------------
    */

    protected function resolveBalanceTransaction(
        ?object $charge
    ): ?object {
        if (!$charge || empty($charge->id)) {
            return null;
        }

        $chargeId = (string) $charge->id;

        /*
        |--------------------------------------------------------------------------
        | 1. Try the Charge field immediately
        |--------------------------------------------------------------------------
        */

        $balanceTransaction = $this->normalizeBalanceTransactionValue(
            $charge->balance_transaction ?? null,
            $chargeId
        );

        if ($balanceTransaction) {
            return $balanceTransaction;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Re-fetch Charge with BalanceTransaction expanded
        |--------------------------------------------------------------------------
        |
        | This handles propagation delay immediately after PaymentIntent
        | success.
        |
        */

        foreach (self::LOOKUP_RETRY_DELAYS as $delay) {
            if ($delay > 0) {
                sleep($delay);
            }

            try {
                $freshCharge = $this->stripe
                    ->charges
                    ->retrieve(
                        $chargeId,
                        [
                            'expand' => ['balance_transaction'],
                        ]
                    );

                $balanceTransaction = $this->normalizeBalanceTransactionValue(
                    $freshCharge->balance_transaction ?? null,
                    $chargeId
                );

                if ($balanceTransaction) {
                    return $balanceTransaction;
                }
            } catch (Throwable $e) {
                Log::warning(
                    'Stripe BalanceTransaction retry failed.',
                    [
                        'charge_id' => $chargeId,
                        'delay_seconds' => $delay,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Last-resort lookup: BalanceTransactions filtered by Charge source
        |--------------------------------------------------------------------------
        |
        | Stripe supports listing balance transactions by their source. This
        | avoids depending entirely on the Charge expandable field.
        |
        */

        foreach (self::LOOKUP_RETRY_DELAYS as $delay) {
            if ($delay > 0) {
                sleep($delay);
            }

            try {
                $transactions = $this->stripe
                    ->balanceTransactions
                    ->all([
                        'source' => $chargeId,
                        'limit' => 10,
                    ]);

                foreach ($transactions->data ?? [] as $candidate) {
                    if (
                        !is_object($candidate)
                        || empty($candidate->id)
                    ) {
                        continue;
                    }

                    if (
                        isset($candidate->source)
                        && (string) $candidate->source !== $chargeId
                    ) {
                        continue;
                    }

                    $complete = $this->retrieveBalanceTransactionById(
                        (string) $candidate->id,
                        $chargeId
                    );

                    if ($complete) {
                        Log::info(
                            'Stripe BalanceTransaction resolved by source lookup.',
                            [
                                'charge_id' => $chargeId,
                                'balance_transaction_id' => $complete->id,
                            ]
                        );

                        return $complete;
                    }
                }
            } catch (Throwable $e) {
                Log::warning(
                    'Stripe BalanceTransaction source lookup failed.',
                    [
                        'charge_id' => $chargeId,
                        'delay_seconds' => $delay,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }

        Log::error(
            'Stripe Charge has no retrievable BalanceTransaction.',
            [
                'charge_id' => $chargeId,
                'balance_transaction_value_type' => get_debug_type(
                    $charge->balance_transaction ?? null
                ),
            ]
        );

        return null;
    }

    protected function normalizeBalanceTransactionValue(
        mixed $value,
        string $chargeId
    ): ?object {
        if (is_object($value) && !empty($value->id)) {
            if (
                isset($value->fee)
                && isset($value->net)
            ) {
                return $value;
            }

            return $this->retrieveBalanceTransactionById(
                (string) $value->id,
                $chargeId
            );
        }

        if (is_string($value) && trim($value) !== '') {
            return $this->retrieveBalanceTransactionById(
                trim($value),
                $chargeId
            );
        }

        return null;
    }

    protected function retrieveBalanceTransactionById(
        string $balanceTransactionId,
        ?string $chargeId = null
    ): ?object {
        try {
            $balanceTransaction = $this->stripe
                ->balanceTransactions
                ->retrieve(
                    $balanceTransactionId
                );

            if (
                !is_object($balanceTransaction)
                || empty($balanceTransaction->id)
            ) {
                return null;
            }

            if (
                !isset($balanceTransaction->fee)
                || !isset($balanceTransaction->net)
            ) {
                Log::warning(
                    'Stripe BalanceTransaction was retrieved but is missing fee or net.',
                    [
                        'balance_transaction_id' => $balanceTransactionId,
                        'charge_id' => $chargeId,
                    ]
                );

                return null;
            }

            return $balanceTransaction;
        } catch (Throwable $e) {
            Log::warning(
                'Could not retrieve Stripe BalanceTransaction.',
                [
                    'balance_transaction_id' => $balanceTransactionId,
                    'charge_id' => $chargeId,
                    'message' => $e->getMessage(),
                ]
            );

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Payment Method Data
    |--------------------------------------------------------------------------
    */

    protected function resolvePaymentMethodData(
        object $paymentIntent,
        ?object $charge
    ): array {
        $paymentMethodId = $this->stripeObjectId(
            $paymentIntent->payment_method ?? null
        );

        $paymentMethodType = null;
        $brand = null;
        $last4 = null;

        $paymentMethodDetails = $charge?->payment_method_details ?? null;

        if (is_object($paymentMethodDetails)) {
            $paymentMethodType = $paymentMethodDetails->type ?? null;

            if (isset($paymentMethodDetails->card)) {
                $brand = $paymentMethodDetails->card->brand ?? null;
                $last4 = $paymentMethodDetails->card->last4 ?? null;
            }
        }

        if (
            $paymentMethodId
            && (
                !$paymentMethodType
                || !$brand
                || !$last4
            )
        ) {
            try {
                $paymentMethod = $this->stripe
                    ->paymentMethods
                    ->retrieve($paymentMethodId);

                $paymentMethodType = $paymentMethodType
                    ?: ($paymentMethod->type ?? null);

                if (isset($paymentMethod->card)) {
                    $brand = $brand
                        ?: ($paymentMethod->card->brand ?? null);
                    $last4 = $last4
                        ?: ($paymentMethod->card->last4 ?? null);
                }
            } catch (Throwable $e) {
                Log::debug(
                    'Stripe payment method details unavailable.',
                    [
                        'payment_method_id' => $paymentMethodId,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }

        return [
            'payment_method_id' => $paymentMethodId,
            'payment_method_type' => $paymentMethodType,
            'brand' => $brand,
            'last4' => $last4,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Failed Transaction
    |--------------------------------------------------------------------------
    */

    protected function recordFailedTransaction(
        Invoice $invoice,
        ?string $paymentIntentId,
        string $errorMessage,
        object $stripeObject
    ): ?Transaction {
        $paymentIntentId = $paymentIntentId
            ? trim($paymentIntentId)
            : null;

        $amountReceived = (int) (
            data_get($stripeObject, 'amount_received') ?? 0
        );

        $amount = $amountReceived > 0
            ? $amountReceived
            : (int) (
                data_get($stripeObject, 'amount')
                ?? data_get($stripeObject, 'amount_paid')
                ?? data_get($stripeObject, 'total')
                ?? 0
            );

        $amount = max($amount, 0);

        if ($paymentIntentId) {
            $existingTransaction = Transaction::query()
                ->where(
                    'stripe_payment_intent_id',
                    $paymentIntentId
                )
                ->first();

            if ($existingTransaction) {
                /*
                 * Never downgrade an already successful transaction because a
                 * delayed failure event arrived later.
                 */
                if ($this->isTransactionSucceeded($existingTransaction)) {
                    Log::warning(
                        'Ignoring Stripe failure event for an already-successful local transaction.',
                        [
                            'transaction_id' => $existingTransaction->id,
                            'invoice_id' => $invoice->id,
                            'payment_intent_id' => $paymentIntentId,
                        ]
                    );

                    return $existingTransaction->fresh();
                }

                $existingTransaction->update([
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $amount,
                    'processing_fee' => 0,
                    'net_amount' => 0,
                    'status' => TransactionStatus::Failed,
                    'paid_at' => null,
                    'error_message' => $errorMessage,
                ]);

                return $existingTransaction->fresh();
            }
        }

        return Transaction::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_charge_id' => null,
            'stripe_balance_transaction_id' => null,
            'stripe_payment_method_id' => null,
            'amount' => $amount,
            'processing_fee' => 0,
            'net_amount' => 0,
            'status' => TransactionStatus::Failed,
            'paid_at' => null,
            'payment_method_type' => null,
            'brand' => null,
            'last4' => null,
            'error_message' => $errorMessage,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Invoice
    |--------------------------------------------------------------------------
    */

    protected function resolveInvoice(
        object $stripeObject
    ): ?Invoice {
        $invoiceId = data_get(
            $stripeObject,
            'metadata.invoice_id'
        );

        if ($invoiceId) {
            $invoice = Invoice::query()
                ->with([
                    'customer',
                    'items',
                    'transactions',
                ])
                ->find($invoiceId);

            if ($invoice) {
                return $invoice;
            }
        }

        $stripeInvoiceId = $this->stripeObjectId(
            $stripeObject->id ?? null
        );

        if (
            $stripeInvoiceId
            && str_starts_with($stripeInvoiceId, 'in_')
        ) {
            $invoice = Invoice::query()
                ->with([
                    'customer',
                    'items',
                    'transactions',
                ])
                ->where(
                    'stripe_invoice_id',
                    $stripeInvoiceId
                )
                ->first();

            if ($invoice) {
                return $invoice;
            }
        }

        $subscriptionId = $this->stripeObjectId(
            $stripeObject->subscription ?? null
        );

        if ($subscriptionId) {
            $invoice = Invoice::query()
                ->with([
                    'customer',
                    'items',
                    'transactions',
                ])
                ->where(
                    'stripe_subscription_id',
                    $subscriptionId
                )
                ->latest('updated_at')
                ->first();

            if ($invoice) {
                return $invoice;
            }
        }

        $stripeCustomerId = $this->stripeObjectId(
            $stripeObject->customer ?? null
        );

        if (
            $stripeCustomerId
            && $stripeInvoiceId
            && str_starts_with($stripeInvoiceId, 'in_')
        ) {
            $customer = Customer::query()
                ->where(
                    'stripe_id',
                    $stripeCustomerId
                )
                ->first();

            if ($customer) {
                $invoice = Invoice::query()
                    ->with([
                        'customer',
                        'items',
                        'transactions',
                    ])
                    ->where(
                        'customer_id',
                        $customer->id
                    )
                    ->where(
                        'stripe_invoice_id',
                        $stripeInvoiceId
                    )
                    ->first();

                if ($invoice) {
                    return $invoice;
                }
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Invoice From PaymentIntent
    |--------------------------------------------------------------------------
    */

    protected function resolveInvoiceFromPaymentIntent(
        object $paymentIntent
    ): ?Invoice {
        $invoiceId = data_get(
            $paymentIntent,
            'metadata.invoice_id'
        );

        if (!$invoiceId) {
            return null;
        }

        return Invoice::query()
            ->with([
                'customer',
                'items',
                'transactions',
            ])
            ->find($invoiceId);
    }

    /*
    |--------------------------------------------------------------------------
    | Sync Invoice Stripe References
    |--------------------------------------------------------------------------
    */

    protected function syncInvoiceStripeReferences(
        Invoice $invoice,
        object $stripeObject
    ): void {
        $updates = [];

        $stripeInvoiceId = $this->stripeObjectId(
            $stripeObject->id ?? null
        );

        if (
            $stripeInvoiceId
            && str_starts_with($stripeInvoiceId, 'in_')
            && $invoice->stripe_invoice_id !== $stripeInvoiceId
        ) {
            $updates['stripe_invoice_id'] = $stripeInvoiceId;
        }

        $stripeSubscriptionId = $this->stripeObjectId(
            $stripeObject->subscription ?? null
        );

        if (
            $stripeSubscriptionId
            && $invoice->stripe_subscription_id !== $stripeSubscriptionId
        ) {
            $updates['stripe_subscription_id'] = $stripeSubscriptionId;
        }

        if ($updates !== []) {
            $invoice->update($updates);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Failure Notifications
    |--------------------------------------------------------------------------
    */

    protected function sendFailureNotifications(
        ?Invoice $invoice,
        object $stripeObject,
        string $errorMessage
    ): void {
        $customerEmail = $this->resolveCustomerEmail(
            $stripeObject
        );

        if ($customerEmail !== null) {
            try {
                Mail::raw(
                    "Your payment attempt was unsuccessful. "
                    . "Please update your payment method to avoid "
                    . "interruption to your recurring services.\n\n"
                    . "We will continue to retry automatically, "
                    . "but please update your billing details as soon "
                    . "as possible.",
                    function ($message) use ($customerEmail): void {
                        $message
                            ->to($customerEmail)
                            ->subject('Payment failed - action required');
                    }
                );
            } catch (Throwable $e) {
                Log::error(
                    'Failed payment customer email failed.',
                    [
                        'customer_email' => $customerEmail,
                        'invoice_id' => $invoice?->id,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }

        $adminEmails = $this->getCheckoutAdminEmails();

        if ($adminEmails === []) {
            $adminEmails = [
                config('mail.admin_address')
                    ?: config(
                        'mail.from.address',
                        'support@clearcodewebsolutions.com'
                    ),
            ];
        }

        foreach ($adminEmails as $adminEmail) {
            try {
                Mail::raw(
                    "A Stripe payment failed for invoice #"
                    . ($invoice?->id ?? 'unknown')
                    . ". Please review the payment method "
                    . "and follow up with the customer.\n\n"
                    . "Stripe error: "
                    . $errorMessage,
                    function ($message) use ($adminEmail): void {
                        $message
                            ->to($adminEmail)
                            ->subject('Payment failed - action required');
                    }
                );
            } catch (Throwable $e) {
                Log::error(
                    'Failed payment admin email failed.',
                    [
                        'email' => $adminEmail,
                        'invoice_id' => $invoice?->id,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }

        Log::warning(
            'Stripe payment failure processed.',
            [
                'customer_email' => $customerEmail,
                'invoice_id' => $invoice?->id,
                'stripe_event_id' => $stripeObject->id ?? null,
                'error_message' => $errorMessage,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Success Notification
    |--------------------------------------------------------------------------
    */

    protected function sendAdminSuccessNotification(
        ?Invoice $invoice
    ): void {
        if (!$invoice) {
            return;
        }

        $adminEmails = $this->getCheckoutAdminEmails();

        if ($adminEmails === []) {
            $adminEmails = [
                config('mail.admin_address')
                    ?: config(
                        'mail.from.address',
                        'support@clearcodewebsolutions.com'
                    ),
            ];
        }

        foreach ($adminEmails as $adminEmail) {
            try {
                Mail::to($adminEmail)->send(
                    new PaymentReceivedAdmin($invoice)
                );
            } catch (Throwable $e) {
                Log::error(
                    'Admin success notification failed.',
                    [
                        'invoice_id' => $invoice->id,
                        'email' => $adminEmail,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Email Addresses
    |--------------------------------------------------------------------------
    */

    protected function getCheckoutAdminEmails(): array
    {
        $emails = [];

        $configured = config(
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

        foreach ((array) $configured as $email) {
            $email = trim((string) $email);

            if (
                $email !== ''
                && filter_var($email, FILTER_VALIDATE_EMAIL)
            ) {
                $emails[] = $email;
            }
        }

        try {
            if (Schema::hasColumn('users', 'role')) {
                $roleEmails = User::query()
                    ->whereIn('role', [
                        'owner',
                        'admin',
                    ])
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->map(
                        fn ($email) => trim((string) $email)
                    )
                    ->filter(
                        fn ($email) => filter_var(
                            $email,
                            FILTER_VALIDATE_EMAIL
                        )
                    )
                    ->all();

                $emails = array_merge(
                    $emails,
                    $roleEmails
                );
            }
        } catch (Throwable $e) {
            Log::debug(
                'Could not resolve role-based checkout admin emails.',
                [
                    'message' => $e->getMessage(),
                ]
            );
        }

        return collect($emails)
            ->map(
                fn ($email) => strtolower(trim((string) $email))
            )
            ->filter(
                fn ($email) => filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            )
            ->unique()
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Customer Email
    |--------------------------------------------------------------------------
    */

    protected function resolveCustomerEmail(
        object $stripeObject
    ): ?string {
        $email = $stripeObject->customer_email ?? null;

        if (
            is_string($email)
            && trim($email) !== ''
        ) {
            return trim($email);
        }

        $customerId = $this->stripeObjectId(
            $stripeObject->customer ?? null
        );

        if (!$customerId) {
            return null;
        }

        try {
            $customer = Customer::query()
                ->where(
                    'stripe_id',
                    $customerId
                )
                ->first();

            if (
                $customer
                && is_string($customer->email)
                && trim($customer->email) !== ''
            ) {
                return trim($customer->email);
            }
        } catch (Throwable $e) {
            Log::debug(
                'Local Stripe customer lookup failed.',
                [
                    'stripe_customer_id' => $customerId,
                    'message' => $e->getMessage(),
                ]
            );
        }

        try {
            $customer = $this->stripe
                ->customers
                ->retrieve($customerId);

            $stripeEmail = $customer->email ?? null;

            return is_string($stripeEmail)
                && trim($stripeEmail) !== ''
                ? trim($stripeEmail)
                : null;
        } catch (Throwable $e) {
            Log::warning(
                'Stripe customer email lookup failed.',
                [
                    'stripe_customer_id' => $customerId,
                    'message' => $e->getMessage(),
                ]
            );

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Failure Message
    |--------------------------------------------------------------------------
    */

    protected function resolveStripeFailureMessage(
        object $stripeObject
    ): string {
        return (string) (
            data_get(
                $stripeObject,
                'last_payment_error.message'
            )
            ?? data_get(
                $stripeObject,
                'failure_message'
            )
            ?? data_get(
                $stripeObject,
                'error.message'
            )
            ?? 'Stripe payment failed.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Transaction Status Helpers
    |--------------------------------------------------------------------------
    */

    protected function isTransactionSucceeded(
        Transaction $transaction
    ): bool {
        $status = $transaction->status;

        if ($status instanceof TransactionStatus) {
            return $status === TransactionStatus::Succeeded;
        }

        return (string) $status === TransactionStatus::Succeeded->value;
    }

    /*
    |--------------------------------------------------------------------------
    | Stripe Object ID
    |--------------------------------------------------------------------------
    */

    protected function stripeObjectId(
        mixed $value
    ): ?string {
        if (
            is_string($value)
            && trim($value) !== ''
        ) {
            return trim($value);
        }

        if (
            is_object($value)
            && isset($value->id)
            && is_string($value->id)
            && trim($value->id) !== ''
        ) {
            return trim($value->id);
        }

        return null;
    }

   /*
|--------------------------------------------------------------------------
| Customer Payment Location
|--------------------------------------------------------------------------
*/

/**
 * Capture the customer's location for a successful payment.
 *
 * This method is intentionally idempotent because Stripe can deliver
 * the same successful payment through multiple webhook events and can
 * retry webhook deliveries.
 */
protected function capturePaymentLocation(
    Customer $customer,
    Transaction $transaction,
    string $ipAddress
): void {
    $ipAddress = trim($ipAddress);

    if ($ipAddress === '') {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Already captured?
    |--------------------------------------------------------------------------
    |
    | A single transaction should only have one payment-location record.
    | This prevents duplicate records when Stripe sends:
    |
    | - payment_intent.succeeded
    | - charge.succeeded
    | - invoice.payment_succeeded
    |
    | or retries any of those events.
    |
    */

    $alreadyCaptured = CustomerPaymentLocation::query()
        ->where('transaction_id', $transaction->id)
        ->exists();

    if ($alreadyCaptured) {
        return;
    }

    try {
        $location = $this->customerLocationService
            ->recordForPayment(
                customer: $customer,
                transaction: $transaction,
                ipAddress: $ipAddress,
            );

        if (!$location) {
            Log::warning(
                'Customer payment location could not be recorded.',
                [
                    'customer_id' => $customer->id,
                    'transaction_id' => $transaction->id,
                    'payment_intent_id' =>
                        $transaction->stripe_payment_intent_id,
                    'ip_address' => $ipAddress,
                ]
            );
        }
    } catch (Throwable $e) {
        /*
        |--------------------------------------------------------------------------
        | Location failure must NOT invalidate a successful payment.
        |--------------------------------------------------------------------------
        |
        | Stripe payment synchronization has already succeeded. A GeoIP
        | provider failure, malformed IP, timeout, etc. should not cause
        | the webhook to fail and potentially trigger unnecessary Stripe
        | retries.
        |
        */

        Log::error(
            'Customer payment location capture failed.',
            [
                'customer_id' => $customer->id,
                'transaction_id' => $transaction->id,
                'payment_intent_id' =>
                    $transaction->stripe_payment_intent_id,
                'ip_address' => $ipAddress,
                'message' => $e->getMessage(),
            ]
        );
    }
}
}
