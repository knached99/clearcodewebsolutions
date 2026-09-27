<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
use App\Mail\PaymentReceivedAdmin;
use App\Mail\PaymentReceiptMail;
use App\Mail\SubscriptionRenewalDecisionMail;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $endpointSecret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $signatureHeader = $request->header('Stripe-Signature');

        try {
            $event = $endpointSecret
                ? Webhook::constructEvent($payload, $signatureHeader, $endpointSecret)
                : json_decode($payload);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook payload could not be decoded.', [
                'message' => $e->getMessage(),
            ]);

            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', [
                'message' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        if (! is_object($event) || ! isset($event->type)) {
            return response('Invalid event payload', 400);
        }

        $eventPayload = $event->data->object ?? null;

        if (! $eventPayload) {
            return response('Missing event payload', 200);
        }

        switch ($event->type) {
            case 'invoice.upcoming':
                $this->handleInvoiceUpcoming($eventPayload);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($eventPayload);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($eventPayload);
                break;

            case 'charge.failed':
                $this->handleInvoicePaymentFailed($eventPayload);
                break;

            case 'payment_intent.payment_failed':
                $this->handleInvoicePaymentFailed($eventPayload);
                break;

            case 'payment_intent.succeeded':
            $this->handlePaymentIntentSucceeded($eventPayload);
            break;
        }

        return response('Webhook handled', 200);
    }

    protected function handleInvoiceUpcoming(object $invoiceObject): void
    {
        $customerEmail = $this->resolveCustomerEmail($invoiceObject);

        if ($customerEmail === null) {
            return;
        }

        $metadata = is_object($invoiceObject->metadata ?? null)
            ? $invoiceObject->metadata
            : (is_array($invoiceObject->metadata ?? null) ? (object) $invoiceObject->metadata : null);

        $invoiceId = $metadata ? (data_get($metadata, 'invoice_id') ?? data_get($invoiceObject, 'metadata.invoice_id')) : null;
        $localInvoice = $invoiceId ? Invoice::find($invoiceId) : null;

        if (! $localInvoice && ! empty($invoiceObject->subscription ?? null)) {
            $localInvoice = Invoice::query()
                ->where('stripe_id', $invoiceObject->subscription)
                ->first();
        }

        if (! $localInvoice && $customerEmail !== null) {
            $localInvoice = Invoice::query()
                ->whereHas('customer', fn ($query) => $query->where('email', $customerEmail))
                ->where('payment_type', PaymentType::Subscription->value)
                ->latest('created_at')
                ->first();
        }

        $isRecurringInvoice = $localInvoice
            && (
                $localInvoice->payment_type === PaymentType::Subscription
                || $localInvoice->payment_type === PaymentType::Subscription->value
            );

        $hasCompletedTwelveMonthMinimum = $localInvoice
            && (int) ($localInvoice->subscription_months ?? 0) >= 12;

        if ($localInvoice && $isRecurringInvoice && $hasCompletedTwelveMonthMinimum) {
            Mail::to($customerEmail)->send(
                new SubscriptionRenewalDecisionMail(
                    invoice: $localInvoice,
                    renewalDate: now()->addDays(10)->format('F j, Y'),
                    monthlyRecurringAmountInCents: $localInvoice->monthlyRecurringAmountInCents(),
                    addonCount: $localInvoice->items()->where('is_subscription', true)->whereNotNull('stripe_price_id')->count(),
                )
            );

            Log::info('12-month subscription renewal decision email sent.', [
                'customer_email' => $customerEmail,
                'invoice_id' => $localInvoice->id,
                'subscription_id' => $invoiceObject->subscription ?? null,
            ]);

            return;
        }

        Mail::raw(
            "Your subscription renewal is coming up soon. Please make sure your card on file is ready for the upcoming billing date.\n\nThank you!",
            function ($message) use ($customerEmail) {
                $message->to($customerEmail)
                    ->subject('Upcoming subscription renewal');
            }
        );

        Log::info('Upcoming renewal notification sent.', [
            'customer_email' => $customerEmail,
            'invoice_id' => $invoiceObject->id ?? null,
        ]);
    }

    protected function handleInvoicePaymentSucceeded(object $invoiceObject): void
    {
        $invoiceId = $invoiceObject->metadata->invoice_id ?? null;
        $invoice = $invoiceId ? Invoice::find($invoiceId) : null;

         Log::info('Stripe invoice.payment_succeeded received.', [
        'stripe_invoice_id' => $invoiceObject->id ?? null,
        'metadata' => isset($invoiceObject->metadata)
            ? (array) $invoiceObject->metadata
            : [],
        'customer' => $invoiceObject->customer ?? null,
        'customer_email' => $invoiceObject->customer_email ?? null,
        'payment_intent' => $invoiceObject->payment_intent ?? null,
    ]);

        if (! $invoice && ! empty($invoiceObject->customer ?? null)) {
            $customer = \App\Models\Customer::query()
                ->where('stripe_id', (string) $invoiceObject->customer)
                ->first();

            if ($customer) {
                $invoice = Invoice::query()
                    ->where('customer_id', $customer->id)
                    ->latest('created_at')
                    ->first();
            }
        }

        if (! $invoice && ! empty($invoiceObject->customer_email ?? null)) {
            $email = trim((string) $invoiceObject->customer_email);

            if ($email !== '') {
                $invoice = Invoice::query()
                    ->whereHas('customer', fn ($query) => $query->where('email', $email))
                    ->latest('created_at')
                    ->first();
            }
        }

        if ($invoice) {
            $invoice->status = InvoiceStatus::Paid;
            $invoice->stripe_id = $invoice->stripe_id ?: ($invoiceObject->subscription ?? $invoiceObject->id ?? $invoice->stripe_id);
            $invoice->save();

            $chargeId = $invoiceObject->charge ?? null;
            $brand = null;
            $last4 = null;
            $processingFee = 0;
            $netAmount = (int) ($invoiceObject->amount_paid ?? $invoiceObject->total ?? 0);

            if ($chargeId) {
                try {
                    $stripe = new StripeClient(config('services.stripe.secret'));
                    $charge = $stripe->charges->retrieve($chargeId, ['expand' => ['balance_transaction']]);

                    if (isset($charge->payment_method_details->card)) {
                        $brand = $charge->payment_method_details->card->brand;
                        $last4 = $charge->payment_method_details->card->last4;
                    }

                    if (isset($charge->balance_transaction)) {
                        $balanceTransaction = $charge->balance_transaction;
                        $processingFee = isset($balanceTransaction->fee)
                            ? -(int) abs((int) $balanceTransaction->fee)
                            : 0;
                        $netAmount = (int) ($balanceTransaction->net ?? ($netAmount + $processingFee));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Could not retrieve Stripe charge for receipt metadata.', [
                        'invoice_id' => $invoice->id,
                        'charge_id' => $chargeId,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            $existingTransaction = Transaction::query()
                ->where('invoice_id', $invoice->id)
                ->where('stripe_payment_intent_id', $invoiceObject->payment_intent ?? null)
                ->first();

            if (! $existingTransaction) {
                Transaction::create([
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'stripe_payment_intent_id' => $invoiceObject->payment_intent ?? null,
                    'amount' => (int) ($invoiceObject->amount_paid ?? $invoiceObject->total ?? 0),
                    'processing_fee' => $processingFee,
                    'net_amount' => $netAmount,
                    'status' => TransactionStatus::Succeeded,
                    'payment_method_type' => 'card',
                    'brand' => $brand,
                    'last4' => $last4,
                ]);
            }
        }

        $customerEmail = $this->resolveCustomerEmail($invoiceObject);

        if ($customerEmail === null) {
            return;
        }

        if ($invoice) {
            Mail::to($customerEmail)->send(new PaymentReceiptMail($invoice));
        } else {
            Mail::raw(
                "Your subscription payment was successful.\n\nThank you for your payment.",
                function ($message) use ($customerEmail) {
                    $message->to($customerEmail)
                        ->subject('Payment received');
                }
            );
        }

        $this->sendAdminSuccessNotification($invoice);

        Log::info('Payment succeeded notification sent.', [
            'customer_email' => $customerEmail,
            'invoice_id' => $invoice?->id ?? $invoiceObject->id ?? null,
        ]);
    }

    protected function handleInvoicePaymentFailed(object $invoiceObject): void
    {
        $invoiceId = $invoiceObject->metadata->invoice_id ?? ($invoiceObject->invoice ?? null);
        $invoice = $invoiceId ? Invoice::find($invoiceId) : null;

        if (! $invoice && ! empty($invoiceObject->customer ?? null)) {
            $customer = \App\Models\Customer::query()
                ->where('stripe_id', (string) $invoiceObject->customer)
                ->first();

            if ($customer) {
                $invoice = Invoice::query()->where('customer_id', $customer->id)->latest('created_at')->first();
            }
        }

        $customerEmail = $this->resolveCustomerEmail($invoiceObject);

        if ($customerEmail !== null) {
            Mail::raw(
                "Your payment attempt was unsuccessful. Please update your payment method to avoid interruption to your recurring services.\n\nWe will continue to retry automatically, but please update your billing details as soon as possible.",
                function ($message) use ($customerEmail) {
                    $message->to($customerEmail)
                        ->subject('Payment failed - action required');
                }
            );
        }

        $adminEmails = $this->getCheckoutAdminEmails();

        if ($adminEmails === []) {
            $adminEmails = [config('mail.admin_address') ?: config('mail.from.address', 'support@clearcodewebsolutions.com')];
        }

        foreach ($adminEmails as $adminEmail) {
            Mail::raw(
                "A Stripe payment failed for invoice #".($invoice?->id ?? $invoiceId ?? 'unknown').". Please review the payment method and follow up with the customer.",
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('Payment failed - action required');
                }
            );
        }

        Log::warning('Stripe payment failed notification sent.', [
            'customer_email' => $customerEmail,
            'invoice_id' => $invoice?->id ?? $invoiceId ?? null,
            'stripe_event' => $invoiceObject->id ?? null,
        ]);
    }

    protected function sendAdminSuccessNotification(?Invoice $invoice): void
    {
        if (! $invoice) {
            return;
        }

        $adminEmails = $this->getCheckoutAdminEmails();

        if ($adminEmails === []) {
            $adminEmails = [config('mail.admin_address') ?: config('mail.from.address', 'support@clearcodewebsolutions.com')];
        }

        foreach ($adminEmails as $adminEmail) {
            try {
                Mail::to($adminEmail)->send(new PaymentReceivedAdmin($invoice));
            } catch (\Throwable $e) {
                Log::error('Admin success notification failed.', [
                    'invoice_id' => $invoice->id,
                    'email' => $adminEmail,
                    'message' => $e->getMessage(),
                ]);
            }
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

        return collect($emails)
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveCustomerEmail(object $invoiceObject): ?string
    {
        $email = $invoiceObject->customer_email ?? null;

        if (is_string($email) && trim($email) !== '') {
            return trim($email);
        }

        $customerId = $invoiceObject->customer ?? null;

        if (! is_string($customerId) || trim($customerId) === '') {
            return null;
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $customer = $stripe->customers->retrieve($customerId);

            return is_string($customer->email ?? null)
                ? trim($customer->email)
                : null;
        } catch (\Throwable $e) {
            Log::warning('Stripe customer email lookup failed during webhook processing.', [
                'customer_id' => $customerId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function handlePaymentIntentSucceeded(object $paymentIntent): void
{
    $invoiceId = data_get(
        $paymentIntent,
        'metadata.invoice_id'
    );

    if (! $invoiceId) {
        Log::warning('Stripe PaymentIntent succeeded without invoice_id metadata.', [
            'payment_intent_id' => $paymentIntent->id ?? null,
        ]);

        return;
    }

    $invoice = Invoice::find($invoiceId);

    if (! $invoice) {
        Log::warning('Local invoice not found for successful PaymentIntent.', [
            'invoice_id' => $invoiceId,
            'payment_intent_id' => $paymentIntent->id ?? null,
        ]);

        return;
    }

    $invoice->status = InvoiceStatus::Paid;

    $invoice->save();

    Log::info('Invoice marked paid from successful Stripe PaymentIntent.', [
        'invoice_id' => $invoice->id,
        'payment_intent_id' => $paymentIntent->id ?? null,
    ]);
}
}
