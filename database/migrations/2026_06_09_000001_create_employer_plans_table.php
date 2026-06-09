<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 32)->unique();
            $table->string('tier', 32)->nullable();
            $table->string('name');
            $table->text('tagline')->nullable();
            $table->unsignedInteger('price_inr')->nullable();
            $table->string('price_sub')->nullable();
            $table->string('cta', 64)->default('Get Started');
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_custom_price')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('billing_period', 16)->default('monthly');
            $table->string('talent_pool_access', 16)->default('limited');
            $table->unsignedInteger('job_credits_included')->nullable();
            $table->boolean('unlimited_profile_unlocks')->default(false);
            $table->unsignedSmallInteger('max_active_jobs')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_plans');
    }
};
