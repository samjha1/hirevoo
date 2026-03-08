<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_referrer_signups', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->unsignedSmallInteger('max_candidates')->default(1)->comment('How many candidates they can refer');
            $table->text('message')->nullable();
            $table->string('source')->default('home')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_referrer_signups');
    }
};
