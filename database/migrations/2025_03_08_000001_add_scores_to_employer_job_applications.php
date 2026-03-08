<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_job_applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('ats_score')->nullable()->after('status')->comment('Resume ATS score 0-100');
            $table->unsignedTinyInteger('job_match_score')->nullable()->after('ats_score')->comment('Job match score 0-100');
            $table->text('job_match_explanation')->nullable()->after('job_match_score');
        });
    }

    public function down(): void
    {
        Schema::table('employer_job_applications', function (Blueprint $table) {
            $table->dropColumn(['ats_score', 'job_match_score', 'job_match_explanation']);
        });
    }
};
