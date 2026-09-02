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
        Schema::create('ticket_verifications', function (Blueprint $table) {
            $table->id();

            /*
             * Email requesting access.
             *
             * Store normalized/lowercase email addresses from
             * the application before creating this record.
             */
            $table->string('email', 255)->index();

            /*
             * Hashed 6-digit verification code.
             *
             * Never store the actual verification code.
             */
            $table->string('code_hash', 255);

            /*
             * Number of failed verification attempts.
             */
            $table->unsignedTinyInteger('attempts')->default(0);

            /*
             * When this verification code expires.
             */
            $table->timestamp('expires_at')->index();

            /*
             * Set when the code is successfully verified.
             */
            $table->timestamp('verified_at')->nullable();

            /*
             * Optional security/auditing information.
             */
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1024)->nullable();

            $table->timestamps();

            /*
             * Helps efficiently find recent verification records
             * for an email.
             */
            $table->index([
                'email',
                'created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_email_verifications');
    }
};