<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employer_job_application_id')
                ->constrained('employer_job_applications')
                ->cascadeOnDelete();

            $table->string('interview_type'); // phone, video, in_person
            $table->string('interviewer_name')->nullable();

            $table->timestamp('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(30);

            // Generated meeting link for Video type (Zoom/Meet/Teams placeholder)
            $table->text('meeting_url')->nullable();

            $table->string('status')->default('scheduled'); // scheduled, cancelled, completed
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
    }
};

