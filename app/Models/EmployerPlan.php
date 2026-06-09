<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmployerPlan extends Model
{
    protected $fillable = [
        'slug',
        'tier',
        'name',
        'tagline',
        'price_inr',
        'price_sub',
        'cta',
        'is_popular',
        'is_custom_price',
        'is_active',
        'sort_order',
        'billing_period',
        'talent_pool_access',
        'job_credits_included',
        'unlimited_profile_unlocks',
        'max_active_jobs',
        'features',
        'extras',
    ];

    protected $casts = [
        'price_inr' => 'integer',
        'is_popular' => 'boolean',
        'is_custom_price' => 'boolean',
        'is_active' => 'boolean',
        'job_credits_included' => 'integer',
        'unlimited_profile_unlocks' => 'boolean',
        'max_active_jobs' => 'integer',
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
        return $this->is_active
            && ! $this->is_custom_price
            && $this->price_inr !== null
            && $this->price_inr > 0;
    }

    /**
     * Shape used by pricing views and checkout (matches legacy config array keys).
     *
     * @return array<string, mixed>
     */
    public function toDisplayArray(): array
    {
        return [
            'tier' => $this->tier,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'price_inr' => $this->price_inr,
            'price_sub' => $this->price_sub,
            'cta' => $this->cta,
            'popular' => $this->is_popular,
            'custom_price' => $this->is_custom_price,
            'features' => $this->features ?? [],
            'talent_pool_access' => $this->talent_pool_access,
            'database_credits_included' => $this->job_credits_included,
            'job_credits_included' => $this->job_credits_included,
            'unlimited_profile_unlocks' => $this->unlimited_profile_unlocks,
            'max_active_jobs' => $this->max_active_jobs,
            'billing_period' => $this->billing_period,
            'extras' => $this->extras ?? [],
        ];
    }
}
