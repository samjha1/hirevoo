<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('skill_analysis_id')->constrained('skill_analysis')->cascadeOnDelete();
            $table->foreignId('job_role_id')->constrained()->cascadeOnDelete();
            $table->integer('match_percentage');
            $table->json('missing_skills')->nullable();
            $table->integer('intent_score')->nullable();
            $table->text('lead_summary')->nullable();
            $table->enum('status', ['available', 'bidding', 'sold', 'contact_unlocked'])->default('available');
            $table->timestamp('bidding_ends_at')->nullable();
            $table->decimal('minimum_bid', 10, 2)->nullable();
            $table->foreignId('won_by_edtech_id')->nullable()->constrained('edtech_profiles')->nullOnDelete();
            $table->decimal('sold_amount', 10, 2)->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('status');
            $table->index('bidding_ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
