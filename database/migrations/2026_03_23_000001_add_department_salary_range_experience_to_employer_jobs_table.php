<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_jobs', function (Blueprint $table) {
            $table->string('job_department')->nullable()->after('company_name');
            $table->unsignedInteger('salary_min')->nullable()->after('pay_type');
            $table->unsignedInteger('salary_max')->nullable()->after('salary_min');
            $table->unsignedInteger('experience_years')->nullable()->after('salary_amount');
        });
    }

    public function down(): void
    {
        Schema::table('employer_jobs', function (Blueprint $table) {
            $table->dropColumn(['job_department', 'salary_min', 'salary_max', 'experience_years']);
        });
    }
};
