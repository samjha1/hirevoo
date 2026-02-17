<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateProfile extends Model
{
    protected $fillable = [
        'user_id', 'headline', 'education', 'experience_years', 'skills',
        'location', 'expected_salary', 'is_premium', 'premium_expires_at',
        'referral_requests_used', 'referral_requests_limit',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'premium_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
