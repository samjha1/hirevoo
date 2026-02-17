<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_required_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_role_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->integer('priority')->default(1);
            $table->timestamps();
        });

        Schema::table('job_required_skills', function (Blueprint $table) {
            $table->index(['job_role_id', 'skill_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_required_skills');
    }
};
