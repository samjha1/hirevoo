<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('leads', 'referral_source')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('referral_source', 100)->nullable()->after('status');
            });
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        // Allow guest referral-intent rows (candidate_id NULL)
        try {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['candidate_id']);
            });
        } catch (\Throwable) {
            // FK name may differ; try raw
        }
        try {
            DB::statement('ALTER TABLE leads MODIFY candidate_id BIGINT UNSIGNED NULL');
        } catch (\Throwable) {
            // Column already nullable
        }
        try {
            Schema::table('leads', function (Blueprint $table) {
                $table->foreign('candidate_id')->references('id')->on('users')->nullOnDelete();
            });
        } catch (\Throwable) {
            // Already exists
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leads', 'referral_source')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('referral_source');
            });
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('DELETE FROM leads WHERE candidate_id IS NULL');
        try {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['candidate_id']);
            });
        } catch (\Throwable) {
        }
        DB::statement('ALTER TABLE leads MODIFY candidate_id BIGINT UNSIGNED NOT NULL');
        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('candidate_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
