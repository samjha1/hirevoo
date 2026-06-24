<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('employer_jobs', 'display_applications_count')) {
                $table->unsignedInteger('display_applications_count')->nullable()->after('apply_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employer_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('employer_jobs', 'display_applications_count')) {
                $table->dropColumn('display_applications_count');
            }
        });
    }
};
