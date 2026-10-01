<?php

namespace Tests\Feature\Teams;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\TeamRole;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\Checkout\StripeCheckout;
use App\Livewire\Teams\Tickets\ChargeCustomerModal;
use App\Mail\PaymentReceivedAdmin;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TeamDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_teams_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('teams.index'));

        $response->assertOk();
    }

    public function test_teams_can_be_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::teams.index')
            ->set('name', 'Test Team')
            ->call('createTeam')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', [
            'name' => 'Test Team',
            'is_personal' => false,
        ]);
    }

    public function test_dashboard_uses_zero_when_no_transaction_records_exist_in_stripe_or_db(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $customer = Customer::create([
            'name' => 'Acme Labs',
            'email' => 'acme@example.com',
        ]);

        $ticket = Ticket::create([
            'team_id' => $team->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-1001',
            'name' => 'Acme Labs',
            'email' => 'acme@example.com',
            'subject' => 'Maintenance',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => 'dashboard-test-guest-token',
        ]);

        Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'abc123dashboardtesttoken',
            'total_amount' => 250000,
            'status' => InvoiceStatus::Paid,
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['current_team' => $team->slug]));

        $response->assertOk();
        $response->assertSee('Acme Labs');
        $response->assertSee('Gross profit');
        $response->assertDontSee('$2,500.00');
    }

    public function test_recurring_addons_force_subscription_invoice_type(): void
    {
        $modal = new ChargeCustomerModal;
        $modal->amount = '100';
        $modal->isSubscription = false;
        $modal->selectedAddonPriceIds = ['price_recurring_addon'];

        $this->assertTrue($modal->hasRecurringInvoiceItems());
        $this->assertSame(PaymentType::Subscription, $modal->resolveInvoicePaymentType());
    }

    public function test_dashboard_service_uses_stripe_success_when_local_records_are_missing(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $customer = Customer::create([
            'name' => 'Acme Labs',
            'email' => 'acme@example.com',
            'stripe_id' => 'cus_stripe_123',
        ]);

        Ticket::create([
            'team_id' => $team->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-9001',
            'name' => 'Acme Labs',
            'email' => 'acme@example.com',
            'subject' => 'Maintenance',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => 'stripe-fallback-test-token',
        ]);

        $stripe = new class
        {
            public object $customers;

            public object $paymentIntents;

            public function __construct()
            {
                $this->customers = new class
                {
                    public function search(array $params): object
                    {
                        return (object) [
                            'data' => [
                                (object) ['id' => 'cus_stripe_123', 'email' => 'acme@example.com'],
                            ],
                        ];
                    }
                };

                $this->paymentIntents = new class
                {
                    public function search(array $params): object
                    {
                        return (object) [
                            'data' => [
                                (object) [
                                    'id' => 'pi_123',
                                    'amount' => 25000,
                                    'status' => 'succeeded',
                                    'created' => now()->timestamp,
                                    'metadata' => (object) ['invoice_id' => 'inv_123'],
                                ],
                            ],
                        ];
                    }

                    public function retrieve(string $id, array $params = []): object
                    {
                        return (object) [
                            'id' => $id,
                            'amount' => 25000,
                            'charges' => (object) [
                                'data' => [
                                    (object) [
                                        'balance_transaction' => (object) [
                                            'fee' => 1600,
                                            'net' => 23400,
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    }
                };
            }
        };

        $service = new TeamDashboardService($stripe);
        $data = $service->dashboardData($team);

        $this->assertSame(25000, $data['grossProfit']);
        $this->assertSame(25000, $data['subscriptionRevenue'] + $data['oneTimeRevenue']);
    }

    public function test_dashboard_separates_one_time_and_subscription_revenue_from_stripe_transactions(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $customer = Customer::create([
            'name' => 'Acme Labs',
            'email' => 'recurring-split@example.com',
            'stripe_id' => 'cus_split_123',
        ]);

        $ticket = Ticket::create([
            'team_id' => $team->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-9003',
            'name' => 'Acme Labs',
            'email' => 'recurring-split@example.com',
            'subject' => 'Split revenue',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => 'stripe-split-token',
        ]);

        $oneTimeInvoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'one-time-stripe-token',
            'total_amount' => 25000,
            'status' => InvoiceStatus::Paid,
            'payment_type' => PaymentType::OneTime,
        ]);

        $subscriptionInvoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'subscription-stripe-token',
            'total_amount' => 125000,
            'status' => InvoiceStatus::Paid,
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
        ]);

        $stripe = new class($oneTimeInvoice, $subscriptionInvoice)
        {
            public object $customers;

            public object $paymentIntents;

            public function __construct($oneTimeInvoice, $subscriptionInvoice)
            {
                $this->customers = new class
                {
                    public function search(array $params): object
                    {
                        return (object) [
                            'data' => [
                                (object) ['id' => 'cus_split_123', 'email' => 'recurring-split@example.com'],
                            ],
                        ];
                    }
                };

                $this->paymentIntents = new class($oneTimeInvoice, $subscriptionInvoice)
                {
                    public function __construct($oneTimeInvoice, $subscriptionInvoice)
                    {
                        $this->oneTimeInvoice = $oneTimeInvoice;
                        $this->subscriptionInvoice = $subscriptionInvoice;
                    }

                    public function search(array $params): object
                    {
                        return (object) [
                            'data' => [
                                (object) [
                                    'id' => 'pi_one_time',
                                    'amount' => 25000,
                                    'status' => 'succeeded',
                                    'created' => now()->timestamp,
                                    'metadata' => (object) ['invoice_id' => $this->oneTimeInvoice->id],
                                ],
                                (object) [
                                    'id' => 'pi_subscription',
                                    'amount' => 125000,
                                    'status' => 'succeeded',
                                    'created' => now()->timestamp,
                                    'metadata' => (object) ['invoice_id' => $this->subscriptionInvoice->id],
                                ],
                            ],
                        ];
                    }

                    public function retrieve(string $id, array $params = []): object
                    {
                        $amount = $id === 'pi_one_time' ? 25000 : 125000;

                        return (object) [
                            'id' => $id,
                            'amount' => $amount,
                            'charges' => (object) [
                                'data' => [
                                    (object) [
                                        'balance_transaction' => (object) [
                                            'fee' => 1600,
                                            'net' => $amount - 1600,
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    }
                };
            }
        };

        $service = new TeamDashboardService($stripe);
        $data = $service->dashboardData($team);

        $this->assertSame(25000, $data['oneTimeRevenue']);
        $this->assertSame(125000, $data['subscriptionRevenue']);
    }

    public function test_dashboard_prefers_stripe_success_over_stale_local_invoice_totals(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $customer = Customer::create([
            'name' => 'Acme Labs',
            'email' => 'acme@example.com',
            'stripe_id' => 'cus_stripe_override',
        ]);

        $ticket = Ticket::create([
            'team_id' => $team->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-9002',
            'name' => 'Acme Labs',
            'email' => 'acme@example.com',
            'subject' => 'Maintenance',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => 'stripe-override-token',
        ]);

        Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'stale-invoice-token',
            'total_amount' => 11014,
            'status' => InvoiceStatus::Paid,
            'payment_type' => PaymentType::OneTime,
        ]);

        $stripe = new class
        {
            public object $customers;

            public object $paymentIntents;

            public function __construct()
            {
                $this->customers = new class
                {
                    public function search(array $params): object
                    {
                        return (object) [
                            'data' => [
                                (object) ['id' => 'cus_stripe_override', 'email' => 'acme@example.com'],
                            ],
                        ];
                    }
                };

                $this->paymentIntents = new class
                {
                    public function search(array $params): object
                    {
                        return (object) [
                            'data' => [
                                (object) [
                                    'id' => 'pi_override',
                                    'amount' => 550700,
                                    'status' => 'succeeded',
                                    'created' => now()->timestamp,
                                    'metadata' => (object) ['invoice_id' => 'inv_override'],
                                ],
                            ],
                        ];
                    }

                    public function retrieve(string $id, array $params = []): object
                    {
                        return (object) [
                            'id' => $id,
                            'amount' => 550700,
                            'charges' => (object) [
                                'data' => [
                                    (object) [
                                        'balance_transaction' => (object) [
                                            'fee' => 16000,
                                            'net' => 534700,
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    }
                };
            }
        };

        $service = new TeamDashboardService($stripe);
        $data = $service->dashboardData($team);

        $this->assertSame(550700, $data['grossProfit']);
        $this->assertSame(550700, $data['oneTimeRevenue']);
    }

    public function test_dashboard_does_not_fallback_to_paid_invoice_totals_when_no_local_or_stripe_transactions_exist(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $customer = Customer::create([
            'name' => 'Acme Labs',
            'email' => 'no-transaction@example.com',
        ]);

        $ticket = Ticket::create([
            'team_id' => $team->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-9004',
            'name' => 'Acme Labs',
            'email' => 'no-transaction@example.com',
            'subject' => 'Silent checkout',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => 'no-transaction-token',
        ]);

        Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'fallback-ticket-token',
            'total_amount' => 16521,
            'status' => InvoiceStatus::Paid,
            'payment_type' => PaymentType::OneTime,
        ]);

        $stripe = new class
        {
            public object $customers;

            public object $paymentIntents;

            public function __construct()
            {
                $this->customers = new class
                {
                    public function search(array $params): object
                    {
                        return (object) ['data' => []];
                    }
                };

                $this->paymentIntents = new class
                {
                    public function search(array $params): object
                    {
                        return (object) ['data' => []];
                    }

                    public function retrieve(string $id, array $params = []): object
                    {
                        return (object) ['id' => $id, 'amount' => 0, 'charges' => (object) ['data' => []]];
                    }
                };
            }
        };

        $service = new TeamDashboardService($stripe);
        $data = $service->dashboardData($team);

        $this->assertSame(0, $data['grossProfit']);
        $this->assertSame(0, $data['oneTimeRevenue']);
        $this->assertSame(0, $data['subscriptionRevenue']);
    }

    public function test_recurring_checkout_requires_signature_before_stripe_form_is_shown(): void
    {
        $customer = Customer::create([
            'name' => 'Signature Customer',
            'email' => 'signature@example.com',
        ]);

        $ticket = Ticket::create([
            'team_id' => Team::factory()->create()->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-SIGN-100',
            'name' => 'Signature Customer',
            'email' => 'signature@example.com',
            'subject' => 'Recurring service agreement',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => 'checkout-signature-test',
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'checkout-signature-token',
            'total_amount' => 150000,
            'status' => InvoiceStatus::Pending,
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Monthly Retainer',
            'amount' => 150000,
            'stripe_price_id' => 'price_recurring_test',
            'is_subscription' => true,
        ]);

        $component = Livewire::test(StripeCheckout::class, ['token' => $invoice->checkout_token]);

        $component->assertSet('signatureRequired', true)
            ->assertSet('hasSignedAgreement', false)
            ->assertSet('clientSecret', null)
            ->assertSee('Service agreement');
    }

    public function test_subscription_receipt_template_includes_recurring_interval_and_renewal_details(): void
    {
        $customer = Customer::create([
            'name' => 'Recurring Customer',
            'email' => 'renewal@example.com',
        ]);

        $ticket = Ticket::create([
            'team_id' => Team::factory()->create()->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-RENEW-1',
            'name' => 'Recurring Customer',
            'email' => 'renewal@example.com',
            'subject' => 'Monthly retainers',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => 'renewal-view-test-token',
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'renewal-view-token',
            'total_amount' => 550700,
            'status' => InvoiceStatus::Paid,
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
            'created_at' => now()->startOfMonth(),
            'updated_at' => now()->startOfMonth(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Development Services',
            'amount' => 500000,
            'is_subscription' => true,
        ]);

        $rendered = view('pdf.invoice', ['invoice' => $invoice->fresh(['customer', 'ticket', 'items'])])->render();

        $this->assertStringContainsString('Next payment', $rendered);
        $this->assertStringContainsString('Renews on', $rendered);
        $this->assertStringContainsString('x12', $rendered);
    }

    public function test_stripe_success_webhook_sends_admin_notification(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'Webhook Customer',
            'email' => 'webhook@example.com',
        ]);

        $ticket = Ticket::create([
            'team_id' => Team::factory()->create()->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TCKT-WEBHOOK-1',
            'name' => 'Webhook Customer',
            'email' => 'webhook@example.com',
            'subject' => 'Webhook sync',
            'status' => 'open',
            'priority' => 'normal',
            'guest_token_hash' => 'webhook-success-test-token',
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'checkout_token' => 'webhook-success-token',
            'total_amount' => 250000,
            'status' => InvoiceStatus::Pending,
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
        ]);

        $controller = app(StripeWebhookController::class);
        $method = new \ReflectionMethod(StripeWebhookController::class, 'handleInvoicePaymentSucceeded');
        $method->setAccessible(true);

        $method->invoke($controller, (object) [
            'id' => 'in_paid_test',
            'customer_email' => 'webhook@example.com',
            'customer' => 'cus_webhook_123',
            'subscription' => 'sub_webhook_123',
            'amount_paid' => 250000,
            'total' => 250000,
            'payment_intent' => 'pi_webhook_123',
            'charge' => 'ch_webhook_123',
            'metadata' => (object) [
                'invoice_id' => $invoice->id,
            ],
        ]);

        Mail::assertSent(PaymentReceivedAdmin::class);
    }

    public function test_personal_team_returns_the_team_owned_by_the_user(): void
    {
        $otherUser = User::factory()->create();
        $user = User::factory()->make();
        $user->save();

        $otherUser->personalTeam()->members()->attach($user, [
            'role' => TeamRole::Member->value,
        ]);

        $personalTeam = Team::factory()->personal()->create();
        $personalTeam->members()->attach($user, [
            'role' => TeamRole::Owner->value,
        ]);

        $this->assertTrue($personalTeam->is($user->personalTeam()));
    }

    public function test_team_slug_uses_next_available_suffix(): void
    {
        $user = User::factory()->create();

        Team::factory()->create(['name' => 'Acme', 'slug' => 'acme']);
        Team::factory()->create(['name' => 'Acme One', 'slug' => 'acme-1']);
        Team::factory()->create(['name' => 'Acme Ten', 'slug' => 'acme-10']);

        $this->actingAs($user);

        Livewire::test('pages::teams.index')
            ->set('name', 'Acme')
            ->call('createTeam')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', [
            'name' => 'Acme',
            'slug' => 'acme-11',
        ]);
    }

    public function test_team_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $response = $this
            ->actingAs($user)
            ->get(route('teams.edit', $team));

        $response->assertOk();
    }

    public function test_teams_can_be_updated_by_owners(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Original Name']);

        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        Livewire::test('pages::teams.edit', ['team' => $team])
            ->set('teamName', 'Updated Name')
            ->call('updateTeam')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_teams_cannot_be_updated_by_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member);

        Livewire::test('pages::teams.edit', ['team' => $team])
            ->set('teamName', 'Updated Name')
            ->call('updateTeam')
            ->assertForbidden();
    }

    public function test_teams_can_be_deleted_by_owners(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('teams', [
            'id' => $team->id,
        ]);
    }

    public function test_team_deletion_requires_name_confirmation(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', 'Wrong Name')
            ->call('deleteTeam')
            ->assertHasErrors(['deleteName']);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'deleted_at' => null,
        ]);
    }

    public function test_deleting_current_team_switches_to_alphabetically_first_remaining_team(): void
    {
        $user = User::factory()->create(['name' => 'Mike']);

        $zuluTeam = Team::factory()->create(['name' => 'Zulu Team']);
        $zuluTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $alphaTeam = Team::factory()->create(['name' => 'Alpha Team']);
        $alphaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $betaTeam = Team::factory()->create(['name' => 'Beta Team']);
        $betaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $user->update(['current_team_id' => $zuluTeam->id]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $zuluTeam])
            ->set('deleteName', $zuluTeam->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('teams', [
            'id' => $zuluTeam->id,
        ]);

        $this->assertEquals($alphaTeam->id, $user->fresh()->current_team_id);
    }

    public function test_deleting_current_team_falls_back_to_personal_team_when_alphabetically_first(): void
    {
        $user = User::factory()->create();
        $personalTeam = $user->personalTeam();
        $team = Team::factory()->create(['name' => 'Zulu Team']);
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $user->update(['current_team_id' => $team->id]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('teams', [
            'id' => $team->id,
        ]);

        $this->assertEquals($personalTeam->id, $user->fresh()->current_team_id);
    }

    public function test_deleting_non_current_team_leaves_current_team_unchanged(): void
    {
        $user = User::factory()->create();
        $personalTeam = $user->personalTeam();
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $user->update(['current_team_id' => $personalTeam->id]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('teams', [
            'id' => $team->id,
        ]);

        $this->assertEquals($personalTeam->id, $user->fresh()->current_team_id);
    }

    public function test_members_can_leave_non_personal_teams(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member);

        Livewire::test('pages::teams.index')
            ->call('leaveTeam', $team->id)
            ->assertHasNoErrors();

        $this->assertFalse($member->fresh()->belongsToTeam($team));
    }

    public function test_leaving_current_team_switches_to_alphabetically_first_remaining_team(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['name' => 'Mike']);

        $zuluTeam = Team::factory()->create(['name' => 'Zulu Team']);
        $zuluTeam->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $zuluTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

        $alphaTeam = Team::factory()->create(['name' => 'Alpha Team']);
        $alphaTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

        $betaTeam = Team::factory()->create(['name' => 'Beta Team']);
        $betaTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

        $member->update(['current_team_id' => $zuluTeam->id]);

        $this->actingAs($member);

        Livewire::test('pages::teams.index')
            ->call('leaveTeam', $zuluTeam->id)
            ->assertHasNoErrors();

        $this->assertFalse($member->fresh()->belongsToTeam($zuluTeam));
        $this->assertEquals($alphaTeam->id, $member->fresh()->current_team_id);
    }

    public function test_personal_teams_cannot_be_left(): void
    {
        $user = User::factory()->create();
        $personalTeam = $user->personalTeam();

        $this->actingAs($user);

        Livewire::test('pages::teams.index')
            ->call('leaveTeam', $personalTeam->id)
            ->assertForbidden();

        $this->assertTrue($user->fresh()->belongsToTeam($personalTeam));
    }

    public function test_team_owners_cannot_leave_their_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

        $this->actingAs($owner);

        Livewire::test('pages::teams.index')
            ->call('leaveTeam', $team->id)
            ->assertForbidden();

        $this->assertTrue($owner->fresh()->belongsToTeam($team));
    }

    public function test_users_cannot_leave_teams_they_dont_belong_to(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::teams.index')
            ->call('leaveTeam', $team->id)
            ->assertForbidden();
    }

    public function test_leave_control_is_only_rendered_for_leaveable_teams(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $leaveableTeam = Team::factory()->create();

        $leaveableTeam->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $leaveableTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member);

        Livewire::test('pages::teams.index')
            ->assertSeeHtml('data-test="team-leave-button"');
    }

    public function test_leave_control_is_not_rendered_for_personal_or_owned_teams(): void
    {
        $user = User::factory()->create();
        $ownedTeam = Team::factory()->create();

        $ownedTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        Livewire::test('pages::teams.index')
            ->assertDontSeeHtml('data-test="team-leave-button"');
    }

    public function test_deleting_team_switches_other_affected_users_to_their_personal_team(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $team = Team::factory()->create();
        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $owner->update(['current_team_id' => $team->id]);
        $member->update(['current_team_id' => $team->id]);

        $this->actingAs($owner);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertEquals($member->personalTeam()->id, $member->fresh()->current_team_id);
    }

    public function test_personal_teams_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $personalTeam = $user->personalTeam();

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $personalTeam])
            ->set('deleteName', $personalTeam->name)
            ->call('deleteTeam')
            ->assertForbidden();

        $this->assertDatabaseHas('teams', [
            'id' => $personalTeam->id,
            'deleted_at' => null,
        ]);
    }

    public function test_teams_cannot_be_deleted_by_non_owners(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertForbidden();
    }

    public function test_guests_cannot_access_teams(): void
    {
        $response = $this->get(route('teams.index'));

        $response->assertRedirect(route('login'));
    }
}
