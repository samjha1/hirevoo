<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->string('current_company')->nullable();
            $table->unsignedTinyInteger('experience_months')->nullable();
            $table->string('preferred_job_location')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('current_salary', 120)->nullable();
            $table->string('expected_salary_currency', 8)->default('INR');
            $table->string('expected_salary_period', 16)->default('per_annum');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'current_company',
                'experience_months',
                'preferred_job_location',
                'linkedin_url',
                'current_salary',
                'expected_salary_currency',
                'expected_salary_period',
            ]);
        });
    }
};
