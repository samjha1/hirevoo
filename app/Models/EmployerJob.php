<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmployerJob extends Model
{
    protected $table = 'employer_jobs';

    protected $fillable = [
        'user_id',
        'company_name',
        'job_department',
        'required_skills',
        'title',
        'slug',
        'job_type',
        'is_night_shift',
        'description',
        'location',
        'work_location_type',
        'pay_type',
        'salary_min',
        'salary_max',
        'salary_amount',
        'experience_years',
        'perks',
        'joining_fee_required',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_night_shift' => 'boolean',
            'joining_fee_required' => 'boolean',
            'required_skills' => 'array',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'experience_years' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(EmployerJobApplication::class, 'employer_job_id');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug) && ! empty($model->title)) {
                $model->slug = Str::slug($model->title) . '-' . substr(uniqid(), -5);
            }
        });
    }
}
