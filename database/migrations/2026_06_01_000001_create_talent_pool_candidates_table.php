<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_pool_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('title')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->string('education')->nullable();
            $table->text('skills')->nullable();
            $table->string('expected_salary')->nullable();
            $table->text('profile_summary')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('resume_url')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('status');
            $table->index('location');
            $table->index('experience_years');
            $table->index('education');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_pool_candidates');
    }
};
