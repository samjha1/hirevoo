<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_plans', function (Blueprint $table) {
            $table->json('extras')->nullable()->after('features');
        });
    }

    public function down(): void
    {
        Schema::table('employer_plans', function (Blueprint $table) {
            $table->dropColumn('extras');
        });
    }
};
