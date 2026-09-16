<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stripe_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('stripe_id')->unique();
            $table->string('type')->default('card'); // card, us_bank_account, cashapp, klarna
            $table->string('brand')->nullable();
            $table->string('last4', 4)->nullable();
            $table->string('exp_month', 2)->nullable();
            $table->string('exp_year', 4)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('stripe_id')->nullable()->index(); // Stripe Invoice ID or Subscription ID
            $table->string('checkout_token', 64)->unique(); // Secure URL token for custom checkout
            $table->integer('total_amount')->default(0); // in cents
            $table->string('status')->default('pending'); // pending, paid, failed, void
            $table->string('payment_type')->default('one_time'); // one_time, subscription
            $table->integer('subscription_months')->nullable(); // min 12 if subscription
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description');
            $table->integer('amount')->default(0); // in cents
            $table->boolean('is_subscription')->default(false);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->integer('amount'); // in cents
            $table->string('status')->default('pending'); // pending, succeeded, failed
            $table->string('payment_method_type')->nullable(); // card, cashapp, etc.
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('customers');
    }
};
