<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentLocationTestSeeder extends Seeder
{
    /**
     * Team whose dashboard we want to test.
     */
    protected string $teamSlug = 'khaled-nacheds-team';

    /**
     * Seed locations.
     *
     * The names are realistic display names for the test dataset.
     *
     * Emails use example.test and IPs use documentation ranges so this
     * dataset remains isolated from real customer information.
     */
    protected array $customers = [

        /*
        |--------------------------------------------------------------------------
        | Texas
        |--------------------------------------------------------------------------
        */

        [
            'name' => 'Marcus Johnson',
            'email' => 'marcus.johnson@example.test',
            'ip' => '192.0.2.10',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'TX',
            'state_name' => 'Texas',
            'city' => 'Houston',
            'postal_code' => '77001',
            'latitude' => 29.7604,
            'longitude' => -95.3698,
            'accuracy_radius' => 20,
            'amount' => 150000,
            'payment_type' => 'one_time',
            'description' => 'Website Development Services',
        ],

        [
            'name' => 'Emily Rodriguez',
            'email' => 'emily.rodriguez@example.test',
            'ip' => '192.0.2.11',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'TX',
            'state_name' => 'Texas',
            'city' => 'Dallas',
            'postal_code' => '75201',
            'latitude' => 32.7767,
            'longitude' => -96.7970,
            'accuracy_radius' => 20,
            'amount' => 35000,
            'payment_type' => 'subscription',
            'description' => 'Monthly Marketing Subscription',
        ],

        [
            'name' => 'James Anderson',
            'email' => 'james.anderson@example.test',
            'ip' => '192.0.2.12',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'TX',
            'state_name' => 'Texas',
            'city' => 'Austin',
            'postal_code' => '78701',
            'latitude' => 30.2672,
            'longitude' => -97.7431,
            'accuracy_radius' => 20,
            'amount' => 275000,
            'payment_type' => 'one_time',
            'description' => 'Custom Software Development',
        ],

        /*
        |--------------------------------------------------------------------------
        | California
        |--------------------------------------------------------------------------
        */

        [
            'name' => 'Sophia Martinez',
            'email' => 'sophia.martinez@example.test',
            'ip' => '192.0.2.13',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'CA',
            'state_name' => 'California',
            'city' => 'Los Angeles',
            'postal_code' => '90001',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'accuracy_radius' => 20,
            'amount' => 75000,
            'payment_type' => 'subscription',
            'description' => 'Monthly Support Subscription',
        ],

        /*
        |--------------------------------------------------------------------------
        | Illinois
        |--------------------------------------------------------------------------
        */

        [
            'name' => 'Michael Reynolds',
            'email' => 'michael.reynolds@example.test',
            'ip' => '192.0.2.14',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'IL',
            'state_name' => 'Illinois',
            'city' => 'Chicago',
            'postal_code' => '60601',
            'latitude' => 41.8781,
            'longitude' => -87.6298,
            'accuracy_radius' => 20,
            'amount' => 425000,
            'payment_type' => 'one_time',
            'description' => 'Enterprise Software Package',
        ],

        /*
        |--------------------------------------------------------------------------
        | Florida
        |--------------------------------------------------------------------------
        */

        [
            'name' => 'Isabella Thompson',
            'email' => 'isabella.thompson@example.test',
            'ip' => '192.0.2.15',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'FL',
            'state_name' => 'Florida',
            'city' => 'Miami',
            'postal_code' => '33101',
            'latitude' => 25.7617,
            'longitude' => -80.1918,
            'accuracy_radius' => 20,
            'amount' => 125000,
            'payment_type' => 'subscription',
            'description' => 'Monthly Consulting Subscription',
        ],

        /*
        |--------------------------------------------------------------------------
        | Connecticut
        |--------------------------------------------------------------------------
        */

        [
            'name' => 'Karam Toor',
            'email' => 'karam.toor@example.test',
            'ip' => '192.0.2.20',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'CT',
            'state_name' => 'Connecticut',
            'city' => 'Cheshire',
            'postal_code' => '06410',
            'latitude' => 41.4987,
            'longitude' => -72.9007,
            'accuracy_radius' => 10,
            'amount' => 185000,
            'payment_type' => 'one_time',
            'description' => 'Cheshire Consulting Project',
        ],

        [
            'name' => 'Sarah Mitchell',
            'email' => 'sarah.mitchell@example.test',
            'ip' => '192.0.2.21',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'CT',
            'state_name' => 'Connecticut',
            'city' => 'New Haven',
            'postal_code' => '06510',
            'latitude' => 41.3083,
            'longitude' => -72.9279,
            'accuracy_radius' => 10,
            'amount' => 95000,
            'payment_type' => 'subscription',
            'description' => 'New Haven Monthly Service',
        ],

        [
            'name' => 'David Brooks',
            'email' => 'david.brooks@example.test',
            'ip' => '192.0.2.22',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'CT',
            'state_name' => 'Connecticut',
            'city' => 'West Haven',
            'postal_code' => '06516',
            'latitude' => 41.2707,
            'longitude' => -72.9470,
            'accuracy_radius' => 10,
            'amount' => 72000,
            'payment_type' => 'one_time',
            'description' => 'West Haven Service Package',
        ],

        [
            'name' => 'Emma Carter',
            'email' => 'emma.carter@example.test',
            'ip' => '192.0.2.23',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'CT',
            'state_name' => 'Connecticut',
            'city' => 'Hartford',
            'postal_code' => '06103',
            'latitude' => 41.7658,
            'longitude' => -72.6734,
            'accuracy_radius' => 10,
            'amount' => 225000,
            'payment_type' => 'one_time',
            'description' => 'Hartford Development Project',
        ],

        [
            'name' => 'Ryan Sullivan',
            'email' => 'ryan.sullivan@example.test',
            'ip' => '192.0.2.24',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'CT',
            'state_name' => 'Connecticut',
            'city' => 'Stamford',
            'postal_code' => '06901',
            'latitude' => 41.0534,
            'longitude' => -73.5387,
            'accuracy_radius' => 10,
            'amount' => 315000,
            'payment_type' => 'subscription',
            'description' => 'Stamford Business Subscription',
        ],

        /*
        |--------------------------------------------------------------------------
        | New York
        |--------------------------------------------------------------------------
        */

        [
            'name' => 'Pablo Rojas',
            'email' => 'pablo.rojas@example.test',
            'ip' => '192.0.2.30',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'NY',
            'state_name' => 'New York',
            'city' => 'Syracuse',
            'postal_code' => '13202',
            'latitude' => 43.0481,
            'longitude' => -76.1474,
            'accuracy_radius' => 10,
            'amount' => 245000,
            'payment_type' => 'one_time',
            'description' => 'Syracuse Development Project',
        ],

        [
            'name' => 'Olivia Hayes',
            'email' => 'olivia.hayes@example.test',
            'ip' => '192.0.2.31',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'NY',
            'state_name' => 'New York',
            'city' => 'Ithaca',
            'postal_code' => '14850',
            'latitude' => 42.4439,
            'longitude' => -76.5019,
            'accuracy_radius' => 10,
            'amount' => 88000,
            'payment_type' => 'subscription',
            'description' => 'Ithaca Monthly Service',
        ],

        [
            'name' => 'Daniel Morgan',
            'email' => 'daniel.morgan@example.test',
            'ip' => '192.0.2.32',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'NY',
            'state_name' => 'New York',
            'city' => 'Albany',
            'postal_code' => '12207',
            'latitude' => 42.6526,
            'longitude' => -73.7562,
            'accuracy_radius' => 10,
            'amount' => 165000,
            'payment_type' => 'one_time',
            'description' => 'Albany Business Services',
        ],

        [
            'name' => 'Sophia Bennett',
            'email' => 'sophia.bennett@example.test',
            'ip' => '192.0.2.33',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'NY',
            'state_name' => 'New York',
            'city' => 'Buffalo',
            'postal_code' => '14202',
            'latitude' => 42.8864,
            'longitude' => -78.8784,
            'accuracy_radius' => 10,
            'amount' => 130000,
            'payment_type' => 'subscription',
            'description' => 'Buffalo Support Subscription',
        ],

        [
            'name' => 'Anthony Walker',
            'email' => 'anthony.walker@example.test',
            'ip' => '192.0.2.34',
            'country_code' => 'US',
            'country_name' => 'United States',
            'state_code' => 'NY',
            'state_name' => 'New York',
            'city' => 'Rochester',
            'postal_code' => '14604',
            'latitude' => 43.1566,
            'longitude' => -77.6088,
            'accuracy_radius' => 10,
            'amount' => 210000,
            'payment_type' => 'one_time',
            'description' => 'Rochester Technology Project',
        ],
    ];

    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Resolve Team
        |--------------------------------------------------------------------------
        */

        $team = Team::query()
            ->where('slug', $this->teamSlug)
            ->first();

        if (! $team) {
            throw new RuntimeException(
                "Could not find team with slug: {$this->teamSlug}"
            );
        }

        $this->command?->info(
            "Using team: {$team->name} ({$team->slug})"
        );

        /*
        |--------------------------------------------------------------------------
        | Remove Previous Test Dataset
        |--------------------------------------------------------------------------
        */

        DB::transaction(function (): void {
            $emails = collect($this->customers)
                ->pluck('email')
                ->all();

            /*
            |--------------------------------------------------------------------------
            | Find seeded customers
            |--------------------------------------------------------------------------
            */

            $customerIds = DB::table('customers')
                ->whereIn('email', $emails)
                ->pluck('id');

            if ($customerIds->isEmpty()) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Find seeded invoices
            |--------------------------------------------------------------------------
            */

            $invoiceIds = DB::table('invoices')
                ->whereIn('customer_id', $customerIds)
                ->pluck('id');

            /*
            |--------------------------------------------------------------------------
            | Find seeded transactions
            |--------------------------------------------------------------------------
            */

            $transactionIds = DB::table('transactions')
                ->whereIn('customer_id', $customerIds)
                ->pluck('id');

            /*
            |--------------------------------------------------------------------------
            | Payment locations
            |--------------------------------------------------------------------------
            */

            if ($transactionIds->isNotEmpty()) {
                DB::table('customer_payment_locations')
                    ->whereIn('transaction_id', $transactionIds)
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Transactions
            |--------------------------------------------------------------------------
            */

            if ($transactionIds->isNotEmpty()) {
                DB::table('transactions')
                    ->whereIn('id', $transactionIds)
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Invoice items
            |--------------------------------------------------------------------------
            */

            if ($invoiceIds->isNotEmpty()) {
                DB::table('invoice_items')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Invoices
            |--------------------------------------------------------------------------
            */

            if ($invoiceIds->isNotEmpty()) {
                DB::table('invoices')
                    ->whereIn('id', $invoiceIds)
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Tickets
            |--------------------------------------------------------------------------
            */

            DB::table('tickets')
                ->whereIn('customer_id', $customerIds)
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | Customers
            |--------------------------------------------------------------------------
            */

            DB::table('customers')
                ->whereIn('id', $customerIds)
                ->delete();
        });

        $this->command?->info(
            'Previous location test dataset removed.'
        );

        /*
        |--------------------------------------------------------------------------
        | Create New Dataset
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($team): void {
            foreach ($this->customers as $index => $data) {
                $this->createCustomerDataset(
                    team: $team,
                    data: $data,
                    index: $index
                );
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $count = count($this->customers);

        $this->command?->newLine();

        $this->command?->info(
            'Payment location dataset created successfully.'
        );

        $this->command?->info("Customers: {$count}");
        $this->command?->info("Tickets: {$count}");
        $this->command?->info("Invoices: {$count}");
        $this->command?->info("Invoice Items: {$count}");
        $this->command?->info("Transactions: {$count}");
        $this->command?->info("Payment Locations: {$count}");
    }

    protected function createCustomerDataset(
        Team $team,
        array $data,
        int $index
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Dates
        |--------------------------------------------------------------------------
        */

        $now = Carbon::now()->subDays(
            count($this->customers) - $index
        );

        /*
        |--------------------------------------------------------------------------
        | IDs
        |--------------------------------------------------------------------------
        */

        $customerId = (string) Str::uuid();
        $invoiceId = (string) Str::uuid();
        $invoiceItemId = (string) Str::uuid();
        $transactionId = (string) Str::uuid();

        /*
        |--------------------------------------------------------------------------
        | Amounts
        |--------------------------------------------------------------------------
        */

        $amount = (int) $data['amount'];

        $processingFee = (int) round(
            ($amount * 0.029) + 30
        );

        $netAmount = max(
            $amount - $processingFee,
            0
        );

        $isSubscription =
            $data['payment_type'] === 'subscription';

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        DB::table('customers')->insert([
            'id' => $customerId,

            'user_id' => null,

            'stripe_id' =>
                'cus_test_' .
                Str::lower(
                    Str::random(16)
                ),

            'pm_type' => 'card',

            'pm_last_four' => '4242',

            'trial_ends_at' => null,

            'name' => $data['name'],

            'email' => $data['email'],

            'created_at' => $now,

            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Ticket
        |--------------------------------------------------------------------------
        */

        $ticketId = DB::table('tickets')->insertGetId([
            'ticket_number' =>
                'LOC-' .
                strtoupper(
                    Str::random(12)
                ),

            'user_id' => null,

            'name' => $data['name'],

            'email' => $data['email'],

            'subject' =>
                'Payment Location - ' .
                $data['city'],

            'status' => 'closed',

            'priority' => 'normal',

            /*
             * Important:
             * This attaches the ticket to the dashboard team.
             */
            'team_id' => $team->id,

            /*
             * This attaches the ticket to this customer.
             */
            'customer_id' => $customerId,

            'guest_token_hash' =>
                hash(
                    'sha256',
                    Str::uuid()->toString()
                ),

            'last_activity_at' => $now,

            'created_at' => $now,

            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Invoice
        |--------------------------------------------------------------------------
        */

        DB::table('invoices')->insert([
            'id' => $invoiceId,

            'customer_id' => $customerId,

            /*
             * This is what makes the invoice belong to the team.
             */
            'ticket_id' => $ticketId,

            'stripe_invoice_id' =>
                'in_test_' .
                Str::lower(
                    Str::random(16)
                ),

            'stripe_subscription_id' =>
                $isSubscription
                    ? 'sub_test_' .
                        Str::lower(
                            Str::random(16)
                        )
                    : null,

            'checkout_token' =>
                Str::random(64),

            'signature_data' => null,

            'signature_signed_at' => null,

            'agreement_version' => null,

            'total_amount' => $amount,

            'total_paid' => $amount,

            'status' => 'paid',

            'payment_type' =>
                $data['payment_type'],

            'subscription_months' =>
                $isSubscription
                    ? 12
                    : null,

            'created_at' => $now,

            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Invoice Item
        |--------------------------------------------------------------------------
        */

        DB::table('invoice_items')->insert([
            'id' => $invoiceItemId,

            'invoice_id' => $invoiceId,

            'description' =>
                $data['description'],

            'amount' => $amount,

            'stripe_price_id' =>
                $isSubscription
                    ? 'price_test_' .
                        Str::lower(
                            Str::random(16)
                        )
                    : null,

            'is_subscription' =>
                $isSubscription,

            'is_addon' => false,

            'stripe_subscription_item_id' =>
                $isSubscription
                    ? 'si_test_' .
                        Str::lower(
                            Str::random(16)
                        )
                    : null,

            'created_at' => $now,

            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Successful Transaction
        |--------------------------------------------------------------------------
        */

        DB::table('transactions')->insert([
            'id' => $transactionId,

            'invoice_id' => $invoiceId,

            'customer_id' => $customerId,

            'stripe_payment_intent_id' =>
                'pi_test_' .
                Str::lower(
                    Str::random(20)
                ),

            'stripe_charge_id' =>
                'ch_test_' .
                Str::lower(
                    Str::random(20)
                ),

            'stripe_balance_transaction_id' =>
                'txn_test_' .
                Str::lower(
                    Str::random(20)
                ),

            'stripe_payment_method_id' =>
                'pm_test_' .
                Str::lower(
                    Str::random(20)
                ),

            'amount' => $amount,

            'processing_fee' => $processingFee,

            'net_amount' => $netAmount,

            'status' => 'succeeded',

            'paid_at' => $now,

            'payment_method_type' => 'card',

            'brand' => 'visa',

            'last4' => '4242',

            'error_message' => null,

            'created_at' => $now,

            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Customer Payment Location
        |--------------------------------------------------------------------------
        */

        DB::table('customer_payment_locations')->insert([
            'customer_id' => $customerId,

            'transaction_id' => $transactionId,

            'ip_address' => $data['ip'],

            'country_code' => $data['country_code'],

            'country_name' => $data['country_name'],

            'state_code' => $data['state_code'],

            'state_name' => $data['state_name'],

            'city' => $data['city'],

            'postal_code' => $data['postal_code'],

            'latitude' => $data['latitude'],

            'longitude' => $data['longitude'],

            'accuracy_radius' => $data['accuracy_radius'],

            'captured_at' => $now,

            'created_at' => $now,

            'updated_at' => $now,
        ]);

        $this->command?->info(
            sprintf(
                '%s — %s — $%s',
                $data['name'],
                $data['city'],
                number_format($amount / 100, 2)
            )
        );
    }
}