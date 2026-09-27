<?php

namespace App\Console\Commands;

use App\Enums\PaymentType;
use App\Http\Controllers\StripeWebhookController;
use App\Mail\SubscriptionRenewalDecisionMail;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use ReflectionMethod;

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
    protected $description = 'Simulate the renewal notification and verify the renewal decision email branch.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = trim(
            (string) $this->option('customer-email')
        ) ?: 'customer@example.com';

        $invoiceId = $this->option('invoice-id');

        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);

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
            $customer = Customer::firstOrCreate(
                ['email' => $email],
                ['name' => 'Test Customer']
            );

            $invoice = Invoice::query()->firstOrCreate(
                [
                    'checkout_token' => 'renewal-test-' . bin2hex(random_bytes(8)),
                ],
                [
                    'customer_id' => $customer->id,
                    'ticket_id' => null,
                    'total_amount' => 2500,
                    'status' => 'paid',
                    'payment_type' => PaymentType::Subscription,
                    'subscription_months' => 12,
                ]
            );

            $invoice->stripe_id = 'sub_test_renewal';
            $invoice->save();

            $invoice->items()->delete();

            $invoice->items()->create([
                'description' => 'Base plan',
                'amount' => 2000,
                'is_subscription' => true,
                'stripe_price_id' => 'price_base_test',
            ]);

            $invoice->items()->create([
                'description' => 'Addon',
                'amount' => 500,
                'is_subscription' => true,
                'stripe_price_id' => 'price_addon_test',
            ]);
        }

        $invoice->refresh();

        $this->newLine();

        $this->info(
            'Testing renewal reminder for invoice: ' . $invoice->id
        );

        $this->info(
            'Customer: ' . ($invoice->customer?->email ?? $email)
        );

        $this->info(
            'Monthly recurring total: $' .
            number_format(
                $invoice->monthlyRecurringAmountInCents() / 100,
                2
            )
        );

        $this->info(
            '12-month minimum reached: ' .
            ($invoice->hasCompletedTwelveMonthMinimum() ? 'yes' : 'no')
        );

        if (!$invoice->hasCompletedTwelveMonthMinimum()) {
            $this->error(
                'The invoice does not meet the 12-month minimum requirement.'
            );

            return self::FAILURE;
        }

        if (!$this->option('send-mail')) {
            Mail::fake();
        }

        $controller = app(StripeWebhookController::class);

        $method = new ReflectionMethod(
            StripeWebhookController::class,
            'handleInvoiceUpcoming'
        );

        $method->setAccessible(true);

        $method->invoke(
            $controller,
            (object) [
                'id' => 'in_test_renewal',
                'customer_email' => $email,
                'subscription' => $invoice->stripe_id ?: 'sub_test_renewal',
                'metadata' => (object) [
                    'invoice_id' => $invoice->id,
                ],
            ]
        );

        if (!$this->option('send-mail')) {
            Mail::assertSent(
                SubscriptionRenewalDecisionMail::class,
                function ($mail) use ($invoice) {
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

        $this->info('Renewal reminder test: PASS');

        return self::SUCCESS;
    }
}