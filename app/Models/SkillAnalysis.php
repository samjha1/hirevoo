<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SkillAnalysis extends Model
{
    protected $table = 'skill_analysis';

    protected $fillable = [
        'user_id',
        'job_role_id',
        'resume_id',
        'match_percentage',
        'matched_skills',
        'missing_skills',
        'learning_roadmap',
        'skill_gap_explanation',
        'intent_score',
    ];

    protected $casts = [
        'matched_skills' => 'array',
        'missing_skills' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class);
    }
}
