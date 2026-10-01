<?php

namespace App\Console\Commands;

use App\Enums\PaymentType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\StripeWebhookService;
use Illuminate\Console\Command;

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
    protected $description =
        'Simulate Stripe webhook payloads for invoice.upcoming and invoice.payment_succeeded without making live Stripe calls.';

    public function __construct(
        protected StripeWebhookService $stripeWebhookService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $eventType =
            trim(
                (string) $this->argument('event')
            );

        $email =
            trim(
                (string) $this->option('customer-email')
            )
            ?: 'customer@example.com';

        $subscriptionId =
            trim(
                (string) $this->option('subscription-id')
            )
            ?: 'sub_test_webhook';

        /*
        |--------------------------------------------------------------------------
        | Resolve / Create Customer
        |--------------------------------------------------------------------------
        */

        $customer =
            Customer::query()
                ->firstOrCreate(
                    ['email' => $email],
                    ['name' => 'Webhook Test Customer']
                );

        /*
        |--------------------------------------------------------------------------
        | Resolve / Create Invoice
        |--------------------------------------------------------------------------
        */

        $invoiceId =
            $this->option('invoice-id');

        if ($invoiceId) {
            $invoice =
                Invoice::query()
                    ->with([
                        'customer',
                        'items',
                        'transactions',
                    ])
                    ->find($invoiceId);

            if (! $invoice) {
                $this->error(
                    "Invoice {$invoiceId} was not found."
                );

                return self::FAILURE;
            }
        } else {
            $invoice =
                Invoice::query()
                    ->create([
                        'customer_id' => $customer->id,

                        'ticket_id' => null,

                        'stripe_invoice_id' => null,

                        'stripe_subscription_id' => $subscriptionId,

                        'total_amount' => 2500,

                        'total_paid' => 2500,

                        'status' => 'paid',

                        'payment_type' => PaymentType::Subscription,

                        'subscription_months' => 12,
                    ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Store Stripe Subscription ID
        |--------------------------------------------------------------------------
        */

        if (
            $invoice->stripe_subscription_id
            !== $subscriptionId
        ) {
            $invoice->update([
                'stripe_subscription_id' => $subscriptionId,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Test Invoice Items
        |--------------------------------------------------------------------------
        */

        $invoice->items()->delete();

        $invoice->items()->create([
            'description' => 'Base recurring plan',

            'amount' => 2000,

            'is_subscription' => true,

            'is_addon' => false,

            'stripe_price_id' => 'price_base_test',

            'stripe_subscription_item_id' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Event
        |--------------------------------------------------------------------------
        */

        switch ($eventType) {
            case 'invoice.upcoming':

                $this->simulateUpcoming(
                    $invoice,
                    $email,
                    $subscriptionId
                );

                break;

            case 'invoice.payment_succeeded':

                $this->simulatePaymentSucceeded(
                    $invoice,
                    $email,
                    $subscriptionId
                );

                break;

            default:
                $this->error(
                    "Unsupported event type: {$eventType}. "
                    .'Use invoice.upcoming or invoice.payment_succeeded.'
                );

                return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Output
        |--------------------------------------------------------------------------
        */

        $this->info(
            'Webhook event simulated: '
            .$eventType
        );

        $this->info(
            'Invoice ID: '
            .$invoice->id
        );

        $this->info(
            'Stripe subscription ID: '
            .(
                $invoice->stripe_subscription_id
                ?? 'none'
            )
        );

        return self::SUCCESS;
    }

    /**
     * Simulate invoice.upcoming without a real Stripe request.
     */
    protected function simulateUpcoming(
        Invoice $invoice,
        string $email,
        string $subscriptionId
    ): void {
        $this->stripeWebhookService
            ->simulateInvoiceUpcoming(
                invoice: $invoice,
                customerEmail: $email,
                subscriptionId: $subscriptionId
            );
    }

    /**
     * Simulate invoice.payment_succeeded without a real Stripe request.
     */
    protected function simulatePaymentSucceeded(
        Invoice $invoice,
        string $email,
        string $subscriptionId
    ): void {
        $this->stripeWebhookService
            ->simulateInvoicePaymentSucceeded(
                invoice: $invoice,
                customerEmail: $email,
                subscriptionId: $subscriptionId
            );
    }
}
