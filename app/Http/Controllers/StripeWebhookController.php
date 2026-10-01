<?php

namespace App\Http\Controllers;

use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Throwable;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeWebhookService $stripeWebhookService
    ) {}

    /**
     * Handle incoming Stripe webhook requests.
     */
    public function handle(Request $request): Response
    {
        try {
            $this->stripeWebhookService->handle(
                payload: $request->getContent(),
                signatureHeader: $request->header('Stripe-Signature')
            );

            return response(
                'Webhook handled',
                200
            );
        } catch (\UnexpectedValueException $e) {
            return response(
                'Invalid payload',
                400
            );
        } catch (SignatureVerificationException $e) {
            return response(
                'Invalid signature',
                400
            );
        } catch (Throwable $e) {
            /*
             * Returning 500 is intentional.
             *
             * Stripe can retry a webhook when processing fails.
             */
            return response(
                'Webhook processing failed',
                500
            );
        }
    }
}
