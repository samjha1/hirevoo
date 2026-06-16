<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CandidatePlan extends Model
{
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('candidate_plans.catalog'));
        static::deleted(fn () => Cache::forget('candidate_plans.catalog'));
    }

    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'price_inr',
        'cta',
        'is_popular',
        'is_active',
        'sort_order',
        'duration_days',
        'referral_requests_limit',
        'features',
        'extras',
    ];

    protected $casts = [
        'price_inr' => 'integer',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'duration_days' => 'integer',
        'referral_requests_limit' => 'integer',
        'features' => 'array',
        'extras' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function isPurchasable(): bool
    {
        return $this->is_active && $this->price_inr > 0;
    }

    /** @return array<string, mixed> */
    public function toDisplayArray(): array
    {
        return [
            'name' => $this->name,
            'tagline' => $this->tagline,
            'price_inr' => $this->price_inr,
            'popular' => $this->is_popular,
            'cta' => $this->cta,
            'duration_days' => $this->duration_days,
            'referral_requests_limit' => $this->referral_requests_limit,
            'features' => $this->features ?? [],
            'extras' => $this->extras ?? [],
        ];
    }
}
