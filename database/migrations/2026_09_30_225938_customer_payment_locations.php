<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payment_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignUuid('transaction_id')
                ->nullable()
                ->unique()
                ->constrained('transactions')
                ->nullOnDelete();

            // Customer IP at the time of checkout/payment.
            $table->ipAddress('ip_address')->nullable();

            // MaxMind geographic data.
            $table->string('country_code', 2)->nullable();
            $table->string('country_name')->nullable();

            $table->string('state_code')->nullable();
            $table->string('state_name')->nullable();

            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();

            // MaxMind coordinates.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // MaxMind's estimated accuracy radius in kilometers.
            $table->unsignedInteger('accuracy_radius')->nullable();

            $table->timestamp('captured_at')->nullable();

            $table->timestamps();

            $table->index([
                'customer_id',
                'captured_at',
            ]);

            $table->index([
                'country_code',
                'state_code',
            ]);

            $table->index([
                'state_code',
                'city',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_locations');
    }
};
