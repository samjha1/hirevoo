<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrer_profiles', function (Blueprint $table) {
            $table->string('profile_photo', 1024)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('referrer_profiles', function (Blueprint $table) {
            $table->string('profile_photo', 255)->nullable()->change();
        });
    }
};
