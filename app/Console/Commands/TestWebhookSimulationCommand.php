<?php

namespace App\Console\Commands;

use App\Enums\PaymentType;
use App\Http\Controllers\StripeWebhookController;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Console\Command;
use ReflectionMethod;

class TestWebhookSimulationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-webhook-simulation
                            {event=invoice.upcoming : The Stripe event to simulate: invoice.upcoming or invoice.payment_succeeded}
                            {--customer-email=customer@example.com : Customer email}
                            {--subscription-id=sub_test_webhook : Stripe subscription ID}
                            {--invoice-id= : Optional local invoice ID to attach to the webhook event}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate Stripe webhook payloads for invoice.upcoming and invoice.payment_succeeded without making live Stripe calls.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $eventType = $this->argument('event');
        $email = trim((string) $this->option('customer-email')) ?: 'customer@example.com';
        $subscriptionId = trim((string) $this->option('subscription-id')) ?: 'sub_test_webhook';

        $customer = Customer::firstOrCreate(
            ['email' => $email],
            ['name' => 'Webhook Test Customer']
        );

        $invoice = Invoice::query()->firstOrCreate(
            ['checkout_token' => 'webhook-test-'.bin2hex(random_bytes(8))],
            [
                'customer_id' => $customer->id,
                'ticket_id' => null,
                'total_amount' => 2500,
                'status' => 'paid',
                'payment_type' => PaymentType::Subscription,
                'subscription_months' => 12,
            ]
        );

        $invoice->stripe_id = $subscriptionId;
        $invoice->save();

        $invoice->items()->delete();
        $invoice->items()->create([
            'description' => 'Base recurring plan',
            'amount' => 2000,
            'is_subscription' => true,
            'stripe_price_id' => 'price_base_test',
        ]);

        switch ($eventType) {
            case 'invoice.upcoming':
                $this->simulateUpcoming($invoice, $email, $subscriptionId);
                break;

            case 'invoice.payment_succeeded':
                $this->simulatePaymentSucceeded($invoice, $email, $subscriptionId);
                break;

            default:
                $this->error("Unsupported event type: {$eventType}. Use invoice.upcoming or invoice.payment_succeeded.");

                return self::FAILURE;
        }

        $this->info('Webhook event simulated: '.$eventType);
        $this->info('Invoice ID: '.$invoice->id);

        return self::SUCCESS;
    }

    protected function simulateUpcoming(Invoice $invoice, string $email, string $subscriptionId): void
    {
        $controller = app(StripeWebhookController::class);
        $method = new ReflectionMethod(StripeWebhookController::class, 'handleInvoiceUpcoming');
        $method->setAccessible(true);

        $method->invoke($controller, (object) [
            'id' => 'in_upcoming_test',
            'customer_email' => $email,
            'subscription' => $subscriptionId,
            'metadata' => (object) [
                'invoice_id' => $invoice->id,
            ],
        ]);
    }

    protected function simulatePaymentSucceeded(Invoice $invoice, string $email, string $subscriptionId): void
    {
        $controller = app(StripeWebhookController::class);
        $method = new ReflectionMethod(StripeWebhookController::class, 'handleInvoicePaymentSucceeded');
        $method->setAccessible(true);

        $method->invoke($controller, (object) [
            'id' => 'in_paid_test',
            'customer_email' => $email,
            'customer' => $invoice->customer?->stripe_id ?? 'cus_test_webhook',
            'subscription' => $subscriptionId,
            'amount_paid' => 2500,
            'total' => 2500,
            'payment_intent' => 'pi_test_webhook',
            'charge' => 'ch_test_webhook',
            'metadata' => (object) [
                'invoice_id' => $invoice->id,
            ],
        ]);
    }
}
