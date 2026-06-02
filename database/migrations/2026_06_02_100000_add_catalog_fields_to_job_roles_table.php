<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_roles', function (Blueprint $table) {
            $table->boolean('is_synthetic')->default(false)->after('is_active');
            $table->string('sector', 64)->nullable()->after('is_synthetic');
            $table->unsignedInteger('open_roles_count')->nullable()->after('sector');
            $table->unsignedTinyInteger('referral_boost_pct')->nullable()->after('open_roles_count');
        });
    }

    public function down(): void
    {
        Schema::table('job_roles', function (Blueprint $table) {
            $table->dropColumn(['is_synthetic', 'sector', 'open_roles_count', 'referral_boost_pct']);
        });
    }
};
