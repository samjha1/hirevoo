<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('upskill_opportunity_id')->nullable()->after('candidate_id')->constrained('upskill_opportunities')->nullOnDelete();
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE leads MODIFY skill_analysis_id BIGINT UNSIGNED NULL, MODIFY job_role_id BIGINT UNSIGNED NULL, MODIFY match_percentage INT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['upskill_opportunity_id']);
        });
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE leads MODIFY skill_analysis_id BIGINT UNSIGNED NOT NULL, MODIFY job_role_id BIGINT UNSIGNED NOT NULL, MODIFY match_percentage INT NOT NULL');
        }
    }
};
