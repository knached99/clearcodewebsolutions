<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('role')->nullable();
            $table->string('status')
            ->default('pending')
            ->index();

        $table->timestamp('moderated_at')
            ->nullable();

            $table->text('message');

            $table->unsignedTinyInteger('rating')
                ->default(5);

            $table->boolean('is_published')
                ->default(false);

            $table->boolean('is_featured')
                ->default(false);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamp('published_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'is_published',
                'is_featured',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};