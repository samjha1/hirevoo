<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSchedule extends Model
{
    protected $table = 'interview_schedules';

    protected $fillable = [
        'employer_job_application_id',
        'interview_type',
        'interviewer_name',
        'scheduled_at',
        'duration_minutes',
        'meeting_url',
        'status',
        'notes',
    ];

    // Make sure `scheduled_at` comes back as a Carbon instance.
    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(EmployerJobApplication::class, 'employer_job_application_id');
    }
}

