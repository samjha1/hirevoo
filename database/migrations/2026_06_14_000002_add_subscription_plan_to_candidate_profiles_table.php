<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->string('subscription_plan', 32)->nullable()->after('is_premium');
            $table->timestamp('subscription_started_at')->nullable()->after('subscription_plan');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->dropColumn(['subscription_plan', 'subscription_started_at']);
        });
    }
};
