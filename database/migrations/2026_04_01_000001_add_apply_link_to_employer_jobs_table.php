<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('employer_jobs', 'apply_link')) {
                $table->string('apply_link')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employer_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('employer_jobs', 'apply_link')) {
                $table->dropColumn('apply_link');
            }
        });
    }
};

