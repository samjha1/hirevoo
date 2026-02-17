<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_role_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'hired', 'reward_paid'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->text('referrer_notes')->nullable();
            $table->boolean('hire_verified')->default(false);
            $table->timestamp('hire_verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('referral_requests', function (Blueprint $table) {
            $table->index(['candidate_id', 'referrer_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_requests');
    }
};
