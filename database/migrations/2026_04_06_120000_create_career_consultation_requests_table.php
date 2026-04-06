<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_role_id')->nullable()->constrained('job_roles')->nullOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained('resumes')->nullOnDelete();
            $table->string('source', 32);
            $table->unsignedTinyInteger('match_percentage')->nullable();
            $table->json('gap_skills')->nullable();
            $table->json('suggested_gap_skills')->nullable();
            $table->json('matched_skills')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('job_role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_consultation_requests');
    }
};
