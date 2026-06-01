<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_talent_pool_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('candidate_source', 32);
            $table->unsignedBigInteger('candidate_ref_id');
            $table->unsignedSmallInteger('credits_spent')->default(1);
            $table->timestamps();

            $table->unique(
                ['employer_user_id', 'candidate_source', 'candidate_ref_id'],
                'employer_talent_pool_unlocks_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_talent_pool_unlocks');
    }
};
