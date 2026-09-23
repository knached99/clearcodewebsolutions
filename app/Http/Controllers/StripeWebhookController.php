<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
use App\Mail\PaymentReceiptMail;
use App\Mail\SubscriptionRenewalDecisionMail;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        if ($invoice) {
            $invoice->status = InvoiceStatus::Paid;
            $invoice->save();

            $chargeId = $invoiceObject->charge ?? null;
            $brand = null;
            $last4 = null;

            if ($chargeId) {
                try {
                    $stripe = new StripeClient(config('services.stripe.secret'));
                    $charge = $stripe->charges->retrieve($chargeId);

                    if (isset($charge->payment_method_details->card)) {
                        $brand = $charge->payment_method_details->card->brand;
                        $last4 = $charge->payment_method_details->card->last4;
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

        Log::info('Payment succeeded notification sent.', [
            'customer_email' => $customerEmail,
            'invoice_id' => $invoice?->id ?? $invoiceObject->id ?? null,
        ]);
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
}
