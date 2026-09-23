<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;

        $response = $this->get(route('dashboard', ['current_team' => $team->slug]));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['current_team' => $team->slug]));

        $response->assertOk();
    }

    public function test_dashboard_displays_recurring_revenue_kpis(): void
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;

        $customer = Customer::create([
            'email' => 'customer@example.com',
            'name' => 'Customer One',
        ]);

        $ticket = Ticket::create([
            'team_id' => $team->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-TEST-1',
            'name' => 'Customer One',
            'email' => 'customer@example.com',
            'subject' => 'Subscription test',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => Str::random(64),
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'test-'.Str::random(64),
            'total_amount' => 12500,
            'status' => InvoiceStatus::Paid,
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
        ]);

        $invoice->items()->create([
            'description' => 'Base recurring plan',
            'amount' => 5000,
            'is_subscription' => true,
            'stripe_price_id' => 'price_base_test',
        ]);

        $invoice->items()->create([
            'description' => 'Addon',
            'amount' => 2500,
            'is_subscription' => true,
            'stripe_price_id' => 'price_addon_test',
        ]);

        Transaction::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'stripe_payment_intent_id' => 'pi_test_123',
            'amount' => 12500,
            'status' => TransactionStatus::Succeeded,
            'payment_method_type' => 'card',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['current_team' => $team->slug]));

        $response
            ->assertOk()
            ->assertSee('MRR')
            ->assertSee('ARR')
            ->assertSee('Gross margin');
    }
}
