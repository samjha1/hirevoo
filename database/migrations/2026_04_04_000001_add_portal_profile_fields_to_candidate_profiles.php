<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('user_id');
            $table->text('bio_summary')->nullable()->after('headline');
            $table->text('career_objective')->nullable()->after('bio_summary');
            $table->date('date_of_birth')->nullable()->after('location');
            $table->string('gender', 32)->nullable()->after('date_of_birth');
            $table->string('github_url')->nullable()->after('linkedin_url');
            $table->string('portfolio_url')->nullable()->after('github_url');
            $table->text('tools')->nullable()->after('skills');
            $table->string('technical_skill_level', 32)->nullable()->after('tools');
            $table->json('work_experience')->nullable()->after('current_company');
            $table->json('education_history')->nullable()->after('education');
            $table->json('projects')->nullable()->after('education_history');
            $table->json('certifications')->nullable()->after('projects');
            $table->string('preferred_job_role')->nullable()->after('preferred_job_location');
            $table->string('job_type', 32)->nullable()->after('preferred_job_role');
            $table->string('notice_period', 64)->nullable()->after('job_type');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo_path',
                'bio_summary',
                'career_objective',
                'date_of_birth',
                'gender',
                'github_url',
                'portfolio_url',
                'tools',
                'technical_skill_level',
                'work_experience',
                'education_history',
                'projects',
                'certifications',
                'preferred_job_role',
                'job_type',
                'notice_period',
            ]);
        });
    }
};
