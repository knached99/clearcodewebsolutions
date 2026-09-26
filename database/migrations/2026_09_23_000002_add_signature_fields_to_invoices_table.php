<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('signature_data')->nullable()->after('checkout_token');
            $table->timestamp('signature_signed_at')->nullable()->after('signature_data');
            $table->string('agreement_version')->nullable()->after('signature_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['signature_data', 'signature_signed_at', 'agreement_version']);
        });
    }
};
