<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('match_score')->nullable()->after('status')->comment('0-100 AI/rule-based resume-job match');
            $table->text('match_score_explanation')->nullable()->after('match_score');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['match_score', 'match_score_explanation']);
        });
    }
};
