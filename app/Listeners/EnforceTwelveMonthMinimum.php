<?php 

namespace App\Listeners;

use Laravel\Cashier\Events\WebhookReceived;
use Stripe\StripeClient;

class EnforceTwelveMonthMinimum
{
    public function handle(WebhookReceived $event)
    {
        if ($event->payload['type'] === 'invoice.payment_succeeded') {
            $invoice = $event->payload['data']['object'];
            $subscriptionId = $invoice['subscription'] ?? null;

            if ($subscriptionId) {
                $stripe = new StripeClient(config('services.stripe.secret'));
                
                $invoices = $stripe->invoices->all([
                    'subscription' => $subscriptionId,
                    'status' => 'paid',
                    'limit' => 13
                ]);

                if (count($invoices->data) >= 12) {
                    // Cancels at the end of the 12th period so they get the full final month
                    $stripe->subscriptions->update($subscriptionId, [
                        'cancel_at_period_end' => true
                    ]);
                }
            }
        }
    }
}