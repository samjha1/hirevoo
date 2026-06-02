<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobRole extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
        'is_synthetic',
        'sector',
        'open_roles_count',
        'referral_boost_pct',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_synthetic' => 'boolean',
        'open_roles_count' => 'integer',
        'referral_boost_pct' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_synthetic', false);
    }

    public function scopeSynthetic(Builder $query): Builder
    {
        return $query->where('is_synthetic', true);
    }

    public function displayOpenRolesCount(): int
    {
        if ($this->open_roles_count !== null && $this->open_roles_count > 0) {
            return (int) $this->open_roles_count;
        }

        return random_int(1001, 25000);
    }

    public function displayReferralBoostPct(): int
    {
        if ($this->referral_boost_pct !== null && $this->referral_boost_pct > 0) {
            return (int) $this->referral_boost_pct;
        }

        return random_int(72, 88);
    }

    public function requiredSkills(): HasMany
    {
        return $this->hasMany(JobRequiredSkill::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
