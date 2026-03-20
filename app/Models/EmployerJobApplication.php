<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerJobApplication extends Model
{
    public const STATUS_APPLIED = 'applied';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_INTERVIEWED = 'interviewed';
    public const STATUS_OFFERED = 'offered';
    public const STATUS_HIRED = 'hired';
    public const STATUS_REJECTED = 'rejected';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_APPLIED     => 'Applied',
            self::STATUS_SHORTLISTED => 'Shortlisted',
            self::STATUS_INTERVIEWED => 'Interviewed',
            self::STATUS_OFFERED     => 'Offered',
            self::STATUS_HIRED       => 'Hired',
            self::STATUS_REJECTED    => 'Rejected',
        ];
    }

    protected $table = 'employer_job_applications';

    protected $fillable = [
        'employer_job_id',
        'user_id',
        'resume_id',
        'cover_message',
        'status',
        'ats_score',
        'job_match_score',
        'job_match_explanation',
    ];

    public function employerJob(): BelongsTo
    {
        return $this->belongsTo(EmployerJob::class, 'employer_job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function interviews()
    {
        return $this->hasMany(InterviewSchedule::class, 'employer_job_application_id');
    }
}
