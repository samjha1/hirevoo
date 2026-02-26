<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upskill_opportunities', function (Blueprint $table) {
            $table->json('skills')->nullable()->after('description')->comment('Skills to upskill in / missing skills for this role');
        });
    }

    public function down(): void
    {
        Schema::table('upskill_opportunities', function (Blueprint $table) {
            $table->dropColumn('skills');
        });
    }
};
