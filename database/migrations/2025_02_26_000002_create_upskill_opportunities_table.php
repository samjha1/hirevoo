<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upskill_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('company_name')->nullable();
            $table->text('description')->nullable();
            $table->string('cta_type')->default('pricing'); // pricing, contact
            $table->string('cta_label')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upskill_opportunities');
    }
};
