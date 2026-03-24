<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employer_jobs')) {
            return;
        }

        Schema::table('employer_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('employer_jobs', 'job_department')) {
                $table->string('job_department')->nullable()->after('company_name');
            }
            if (! Schema::hasColumn('employer_jobs', 'required_skills')) {
                $table->json('required_skills')->nullable()->after('job_department');
            }
            if (! Schema::hasColumn('employer_jobs', 'salary_min')) {
                $table->unsignedInteger('salary_min')->nullable()->after('pay_type');
            }
            if (! Schema::hasColumn('employer_jobs', 'salary_max')) {
                $table->unsignedInteger('salary_max')->nullable()->after('salary_min');
            }
            if (! Schema::hasColumn('employer_jobs', 'experience_years')) {
                $table->unsignedInteger('experience_years')->nullable()->after('salary_amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('employer_jobs')) {
            return;
        }

        Schema::table('employer_jobs', function (Blueprint $table) {
            $drop = [];
            foreach (['required_skills', 'job_department', 'salary_min', 'salary_max', 'experience_years'] as $column) {
                if (Schema::hasColumn('employer_jobs', $column)) {
                    $drop[] = $column;
                }
            }
            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
