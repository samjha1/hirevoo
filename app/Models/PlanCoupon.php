<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PlanCoupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'discount_percent',
        'is_active',
        'max_uses',
        'uses_count',
        'valid_from',
        'valid_until',
        'applicable_plan_slugs',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'applicable_plan_slugs' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isValidNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from !== null && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until !== null && $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function appliesToPlan(string $planKey): bool
    {
        $slugs = $this->applicable_plan_slugs;

        if ($slugs === null || $slugs === []) {
            return true;
        }

        return in_array(strtolower(trim($planKey)), array_map('strtolower', $slugs), true);
    }

    public function incrementUsage(): void
    {
        $this->increment('uses_count');
    }
}
