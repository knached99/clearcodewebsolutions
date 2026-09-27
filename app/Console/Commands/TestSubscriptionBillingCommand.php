<?php

namespace App\Console\Commands;

use App\Enums\PaymentType;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Console\Command;

class TestSubscriptionBillingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:test-subscription-billing
                            {--customer-email=customer@example.com : Customer email to use for the test invoice}
                            {--base-amount=2000 : Base recurring monthly amount in cents}
                            {--addon-amounts=500,750 : Comma-separated addon amounts in cents}';

    /**
     * The console command description.
     */
    protected $description = 'Create a sample subscription invoice and print the 12-month billing totals for validation.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = trim((string) $this->option('customer-email')) ?: 'customer@example.com';

        $baseAmount = max(
            0,
            (int) $this->option('base-amount')
        );

        $addonAmounts = $this->parseAddonAmounts(
            (string) $this->option('addon-amounts')
        );

        $customer = Customer::firstOrCreate(
            ['email' => $email],
            ['name' => 'Test Customer']
        );

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => null,
            'checkout_token' => 'test-' . bin2hex(random_bytes(10)),
            'total_amount' => $baseAmount + array_sum($addonAmounts),
            'status' => 'paid',
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
        ]);

        $invoice->items()->delete();

        $invoice->items()->create([
            'description' => 'Base recurring plan',
            'amount' => $baseAmount,
            'is_subscription' => true,
            'stripe_price_id' => 'price_base_test',
        ]);

        foreach ($addonAmounts as $index => $addonAmount) {
            $invoice->items()->create([
                'description' => 'Addon ' . ($index + 1),
                'amount' => $addonAmount,
                'is_subscription' => true,
                'stripe_price_id' => 'price_addon_' . ($index + 1) . '_test',
            ]);
        }

        $invoice->refresh();

        $monthlyRecurring = $invoice->monthlyRecurringAmountInCents();
        $twelveMonthMinimum = $invoice->estimated12MonthValueInCents();
        $hasAddons = $invoice->hasRecurringAddons();
        $hasCompletedMinimum = $invoice->hasCompletedTwelveMonthMinimum();

        $this->newLine();

        $this->info('Subscription billing test created successfully.');

        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                [
                    'Invoice ID',
                    $invoice->id,
                ],
                [
                    'Customer',
                    $customer->email,
                ],
                [
                    'Subscription items',
                    (string) $invoice->items()->count(),
                ],
                [
                    'Monthly recurring',
                    '$' . number_format($monthlyRecurring / 100, 2),
                ],
                [
                    '12-month minimum total',
                    '$' . number_format($twelveMonthMinimum / 100, 2),
                ],
                [
                    'Has recurring addons',
                    $hasAddons ? 'yes' : 'no',
                ],
                [
                    'Has completed 12-month minimum',
                    $hasCompletedMinimum ? 'yes' : 'no',
                ],
            ]
        );

        $this->newLine();

        if (!$hasCompletedMinimum) {
            $this->error(
                'The invoice does not meet the 12-month minimum requirement.'
            );

            return self::FAILURE;
        }

        $this->info(
            '12-month minimum requirement: PASS'
        );

        return self::SUCCESS;
    }

    /**
     * Parse comma-separated addon amounts.
     */
    protected function parseAddonAmounts(string $input): array
    {
        if (trim($input) === '') {
            return [];
        }

        $values = array_map(
            fn ($value) => max(
                0,
                (int) trim((string) $value)
            ),
            explode(',', $input)
        );

        return array_values(
            array_filter(
                $values,
                fn ($value) => $value > 0
            )
        );
    }
}