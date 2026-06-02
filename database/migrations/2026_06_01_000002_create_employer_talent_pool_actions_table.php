<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_talent_pool_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('candidate_source', 32);
            $table->unsignedBigInteger('candidate_ref_id');
            $table->boolean('is_saved')->default(false);
            $table->boolean('is_shortlisted')->default(false);
            $table->timestamps();

            $table->unique(
                ['employer_user_id', 'candidate_source', 'candidate_ref_id'],
                'employer_talent_pool_actions_unique'
            );
            $table->index(['employer_user_id', 'is_saved'], 'etpa_employer_saved_idx');
            $table->index(['employer_user_id', 'is_shortlisted'], 'etpa_employer_shortlisted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_talent_pool_actions');
    }
};
