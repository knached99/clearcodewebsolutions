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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');

            $table->string('subject')->nullable();

            $table->string('status')->default('open');
            $table->string('priority')->default('normal');

            $table->string('guest_token_hash', 64)->unique();

            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();
            $table->index('user_id');
            $table->index(['email']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
