<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Stripe Customer ID
            $table->string('stripe_id')
                ->nullable()
                ->unique();

            // Laravel Cashier
            $table->string('pm_type')
                ->nullable();

            $table->string('pm_last_four')
                ->nullable();

            $table->timestamp('trial_ends_at')
                ->nullable();

            $table->string('name');
            $table->string('email')->unique();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Methods
        |--------------------------------------------------------------------------
        */
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Stripe PaymentMethod ID
            $table->string('stripe_id')->unique();

            // card, us_bank_account, cashapp, klarna, etc.
            $table->string('type')->default('card');

            $table->string('brand')->nullable();
            $table->string('last4', 4)->nullable();

            $table->string('exp_month', 2)->nullable();
            $table->string('exp_year', 4)->nullable();

            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index(['customer_id', 'is_default']);
        });

        /*
        |--------------------------------------------------------------------------
        | Invoices
        |--------------------------------------------------------------------------
        */
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('ticket_id')
                ->nullable()
                ->constrained('tickets')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Stripe references
            |--------------------------------------------------------------------------
            |
            | Keep these separate. A Stripe Invoice ID and Stripe Subscription ID
            | represent different Stripe resources.
            |
            */
            $table->string('stripe_invoice_id')
                ->nullable()
                ->unique();

            $table->string('stripe_subscription_id')
                ->nullable()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Checkout / Agreement
            |--------------------------------------------------------------------------
            */
            $table->string('checkout_token', 64)
                ->unique();

            $table->text('signature_data')
                ->nullable();

            $table->timestamp('signature_signed_at')
                ->nullable();

            $table->string('agreement_version')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Amounts
            |--------------------------------------------------------------------------
            |
            | All monetary values are stored in cents.
            |
            | total_amount = what the invoice/customer owes
            | total_paid   = cached total of successful transactions
            |
            */
            $table->unsignedBigInteger('total_amount')
                ->default(0);

            $table->unsignedBigInteger('total_paid')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            |
            | pending = nothing paid
            | partial = some amount paid, but balance remains
            | paid    = fully paid
            | failed  = payment attempt failed / invoice failed
            | void    = invoice cancelled/voided
            |
            */
            $table->string('status')
                ->default('pending')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Payment Type
            |--------------------------------------------------------------------------
            */
            $table->string('payment_type')
                ->default('one_time')
                ->index();

            // Minimum 12 if subscription
            $table->unsignedSmallInteger('subscription_months')
                ->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });

        /*
        |--------------------------------------------------------------------------
        | Invoice Items
        |--------------------------------------------------------------------------
        */
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->string('description');

            // Amount in cents
            $table->unsignedBigInteger('amount')
                ->default(0);

            // Stripe Price ID
            $table->string('stripe_price_id')
                ->nullable()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Recurring billing
            |--------------------------------------------------------------------------
            |
            | is_subscription:
            |   true  = recurring Stripe subscription item
            |   false = one-time/base non-recurring item
            |
            | is_addon:
            |   true  = recurring addon
            |   false = base item
            |
            */
            $table->boolean('is_subscription')
                ->default(false);

            $table->boolean('is_addon')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Stripe Subscription Item
            |--------------------------------------------------------------------------
            |
            | This lets you map recurring addons directly to the Stripe
            | subscription item that bills them.
            |
            */
            $table->string('stripe_subscription_item_id')
                ->nullable()
                ->index();

            $table->timestamps();

            $table->index([
                'invoice_id',
                'is_subscription',
                'is_addon',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Stripe PaymentIntent
            |--------------------------------------------------------------------------
            |
            | This is the primary idempotency key for Stripe transactions.
            |
            */
            $table->string('stripe_payment_intent_id')
                ->nullable()
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Stripe Charge / Balance Transaction
            |--------------------------------------------------------------------------
            */
            $table->string('stripe_charge_id')
                ->nullable()
                ->index();

            $table->string('stripe_balance_transaction_id')
                ->nullable()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Stripe Payment Method
            |--------------------------------------------------------------------------
            */
            $table->string('stripe_payment_method_id')
                ->nullable()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Amounts
            |--------------------------------------------------------------------------
            |
            | All values are cents.
            |
            | amount:
            |   actual amount received from Stripe
            |
            | processing_fee:
            |   positive Stripe fee
            |
            | net_amount:
            |   amount - processing_fee
            |
            */
            $table->unsignedBigInteger('amount');

            $table->unsignedBigInteger('processing_fee')
                ->default(0);

            $table->unsignedBigInteger('net_amount')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Transaction Status
            |--------------------------------------------------------------------------
            |
            | pending
            | succeeded
            | failed
            |
            */
            $table->string('status')
                ->default('pending')
                ->index();

            $table->timestamp('paid_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Payment Method Details
            |--------------------------------------------------------------------------
            |
            | These are denormalized from Stripe for quick display/reporting.
            |
            */
            $table->string('payment_method_type')
                ->nullable();

            $table->string('brand')
                ->nullable();

            $table->string('last4', 4)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Failure information
            |--------------------------------------------------------------------------
            */
            $table->text('error_message')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Important indexes
            |--------------------------------------------------------------------------
            |
            | Used frequently for:
            |   - invoice payment totals
            |   - dashboard revenue calculations
            |   - successful transaction queries
            |
            */
            $table->index([
                'invoice_id',
                'status',
            ]);

            $table->index([
                'customer_id',
                'status',
            ]);

            $table->index([
                'paid_at',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('customers');
    }
};
