<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'candidate_id',
        'skill_analysis_id',
        'job_role_id',
        'upskill_opportunity_id',
        'match_percentage',
        'missing_skills',
        'intent_score',
        'lead_summary',
        'status',
        'bidding_ends_at',
        'minimum_bid',
        'won_by_edtech_id',
        'sold_amount',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'missing_skills' => 'array',
            'bidding_ends_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function skillAnalysis(): BelongsTo
    {
        return $this->belongsTo(SkillAnalysis::class);
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class);
    }

    public function upskillOpportunity(): BelongsTo
    {
        return $this->belongsTo(UpskillOpportunity::class);
    }
}
