<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edtech_id')->constrained('edtech_profiles')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['placed', 'won', 'lost', 'cancelled'])->default('placed');
            $table->timestamps();
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->index(['lead_id', 'amount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
