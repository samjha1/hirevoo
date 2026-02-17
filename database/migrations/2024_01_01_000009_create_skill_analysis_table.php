<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_analysis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // candidate
            $table->foreignId('job_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('match_percentage');
            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->text('learning_roadmap')->nullable();
            $table->text('skill_gap_explanation')->nullable();
            $table->integer('intent_score')->nullable(); // candidate intent for upskilling
            $table->timestamps();
        });

        Schema::table('skill_analysis', function (Blueprint $table) {
            $table->index(['user_id', 'job_role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_analysis');
    }
};
