<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_job_applications', function (Blueprint $table) {
            $table->string('notice_period', 32)->nullable()->after('cover_message');
            $table->timestamp('info_accurate_confirmed_at')->nullable()->after('notice_period');
        });
    }

    public function down(): void
    {
        Schema::table('employer_job_applications', function (Blueprint $table) {
            $table->dropColumn(['notice_period', 'info_accurate_confirmed_at']);
        });
    }
};
