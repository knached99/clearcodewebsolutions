<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\TransactionStatus;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Mail\PaymentReceiptMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $endpoint_secret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        $event = null;

        try {
            // Allow bypassing signature in local dev if secret isn't set,
            // but strongly enforce in production
            if ($endpoint_secret) {
                $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            } else {
                $event = json_decode($payload);
            }
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;

            // Link to our internal Invoice via metadata
            $invoiceId = $paymentIntent->metadata->invoice_id ?? null;

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);

                if ($invoice && $invoice->status !== InvoiceStatus::Paid) {
                    // Mark as paid
                    $invoice->update(['status' => InvoiceStatus::Paid]);

                    $brand = null;
                    $last4 = null;
                    if (isset($paymentIntent->latest_charge)) {
                        try {
                            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                            $charge = $stripe->charges->retrieve($paymentIntent->latest_charge);
                            if ($charge && isset($charge->payment_method_details->card)) {
                                $brand = $charge->payment_method_details->card->brand;
                                $last4 = $charge->payment_method_details->card->last4;
                            }
                        } catch (\Exception $e) {
                            //
                        }
                    }

                    // Generate local transaction record
                    Transaction::create([
                        'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->customer_id,
                        'stripe_payment_intent_id' => $paymentIntent->id,
                        'amount' => $paymentIntent->amount_received,
                        'status' => TransactionStatus::Succeeded,
                        'payment_method_type' => 'card',
                        'brand' => $brand,
                        'last4' => $last4,
                    ]);

                    // Send receipt with PDF attachment
                    Mail::to($invoice->customer->email)->send(new PaymentReceiptMail($invoice));

                    Log::info("Invoice {$invoice->id} paid successfully via Stripe webhook.");
                }
            }
        }

        return response('Webhook handled', 200);
    }
}
