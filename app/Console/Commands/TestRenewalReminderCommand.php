<?php

namespace App\Console\Commands;

use App\Enums\PaymentType;
use App\Mail\SubscriptionRenewalDecisionMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\StripeWebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestRenewalReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:test-renewal-reminder
                            {--customer-email=customer@example.com : Customer email for the renewal reminder test}
                            {--send-mail : Actually send the renewal email instead of using Mail::fake()}
                            {--invoice-id= : Optional existing invoice ID to use instead of creating a test invoice}';

    /**
     * The console command description.
     */
    protected $description =
        'Simulate the renewal notification and verify the renewal decision email branch.';

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
        $email = trim(
            (string) $this->option('customer-email')
        ) ?: 'customer@example.com';

        $invoiceId = $this->option('invoice-id');

        /*
        |--------------------------------------------------------------------------
        | Existing Invoice
        |--------------------------------------------------------------------------
        */

        if ($invoiceId) {
            $invoice = Invoice::query()
                ->with([
                    'customer',
                    'items',
                ])
                ->find($invoiceId);

            if (!$invoice) {
                $this->error(
                    "Invoice {$invoiceId} was not found."
                );

                return self::FAILURE;
            }

            $customer = $invoice->customer;

            if (!$customer) {
                $this->error(
                    "Invoice {$invoiceId} does not have a customer."
                );

                return self::FAILURE;
            }

            $email = $customer->email;
        } else {
            /*
            |--------------------------------------------------------------------------
            | Create Test Customer
            |--------------------------------------------------------------------------
            */

            $customer = Customer::query()
                ->firstOrCreate(
                    ['email' => $email],
                    ['name' => 'Test Customer']
                );

            /*
            |--------------------------------------------------------------------------
            | Create Test Invoice
            |--------------------------------------------------------------------------
            */

            $invoice = Invoice::query()
                ->create([
                    'customer_id' => $customer->id,
                    'ticket_id' => null,
                    'stripe_invoice_id' => null,
                    'stripe_subscription_id' => 'sub_test_renewal',
                    'total_amount' => 2500,
                    'total_paid' => 2500,
                    'status' => 'paid',
                    'payment_type' => PaymentType::Subscription,
                    'subscription_months' => 12,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Test Items
            |--------------------------------------------------------------------------
            */

            $invoice->items()->delete();

            $invoice->items()->create([
                'description' => 'Base plan',
                'amount' => 2000,
                'is_subscription' => true,
                'is_addon' => false,
                'stripe_price_id' => 'price_base_test',
                'stripe_subscription_item_id' => null,
            ]);

            $invoice->items()->create([
                'description' => 'Addon',
                'amount' => 500,
                'is_subscription' => true,
                'is_addon' => true,
                'stripe_price_id' => 'price_addon_test',
                'stripe_subscription_item_id' => null,
            ]);
        }

        $invoice->refresh();
        $invoice->load([
            'customer',
            'items',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Output Test Information
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'Testing renewal reminder for invoice: '
            . $invoice->id
        );

        $this->info(
            'Customer: '
            . (
                $invoice->customer?->email
                ?? $email
            )
        );

        $this->info(
            'Stripe subscription ID: '
            . (
                $invoice->stripe_subscription_id
                ?? 'none'
            )
        );

        $this->info(
            'Monthly recurring total: $'
            . number_format(
                $invoice->monthlyRecurringAmountInCents() / 100,
                2
            )
        );

        $this->info(
            '12-month minimum reached: '
            . (
                $invoice->hasCompletedTwelveMonthMinimum()
                    ? 'yes'
                    : 'no'
            )
        );

        if (
            !$invoice->hasCompletedTwelveMonthMinimum()
        ) {
            $this->error(
                'The invoice does not meet the 12-month minimum requirement.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Mail Fake
        |--------------------------------------------------------------------------
        */

        if (!$this->option('send-mail')) {
            Mail::fake();
        }

        /*
        |--------------------------------------------------------------------------
        | Simulate invoice.upcoming
        |--------------------------------------------------------------------------
        */

        $this->stripeWebhookService
            ->simulateInvoiceUpcoming(
                invoice: $invoice,
                customerEmail: $email
            );

        /*
        |--------------------------------------------------------------------------
        | Verify Mail
        |--------------------------------------------------------------------------
        */

        if (!$this->option('send-mail')) {
            Mail::assertSent(
                SubscriptionRenewalDecisionMail::class,
                function ($mail) use ($invoice): bool {
                    return $mail->invoice->id === $invoice->id;
                }
            );

            $this->info(
                'Mail fake confirmed the renewal decision email was sent/queued.'
            );
        } else {
            $this->info(
                'Actual email send requested; the renewal decision email was sent.'
            );
        }

        $this->newLine();

        $this->info(
            'Renewal reminder test: PASS'
        );

        return self::SUCCESS;
    }
}