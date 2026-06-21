<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrer_profiles', function (Blueprint $table) {
            $table->unsignedInteger('talent_pool_tokens')->default(0)->after('credits');
        });
    }

    public function down(): void
    {
        Schema::table('referrer_profiles', function (Blueprint $table) {
            $table->dropColumn('talent_pool_tokens');
        });
    }
};
