<?php

namespace Tests\Feature;

use App\Enums\PaymentType;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeRenewalDecisionEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_tracks_completed_12_month_subscription_minimum_and_addons(): void
    {
        $customer = Customer::create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => null,
            'checkout_token' => 'token-1234567890abcdef',
            'total_amount' => 2500,
            'status' => 'paid',
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
        ]);

        $invoice->stripe_id = 'sub_123';
        $invoice->save();

        $invoice->items()->create([
            'description' => 'Core monthly recurring plan',
            'amount' => 2000,
            'is_subscription' => true,
            'stripe_price_id' => 'price_123',
        ]);

        $invoice->items()->create([
            'description' => 'Analytics add-on',
            'amount' => 500,
            'is_subscription' => true,
            'stripe_price_id' => 'price_456',
        ]);

        $this->assertTrue($invoice->hasCompletedTwelveMonthMinimum());
        $this->assertTrue($invoice->hasRecurringAddons());
        $this->assertSame(2500, $invoice->monthlyRecurringAmountInCents());
    }
}
