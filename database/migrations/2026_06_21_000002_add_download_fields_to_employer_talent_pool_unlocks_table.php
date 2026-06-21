<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_talent_pool_unlocks', function (Blueprint $table) {
            $table->unsignedSmallInteger('download_tokens_spent')->default(0)->after('credits_spent');
            $table->timestamp('downloaded_at')->nullable()->after('download_tokens_spent');
        });
    }

    public function down(): void
    {
        Schema::table('employer_talent_pool_unlocks', function (Blueprint $table) {
            $table->dropColumn(['download_tokens_spent', 'downloaded_at']);
        });
    }
};
