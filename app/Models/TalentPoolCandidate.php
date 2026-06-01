<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TalentPoolCandidate extends Model
{
    public const SOURCE = 'talent_pool';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'full_name',
        'title',
        'location',
        'experience_years',
        'education',
        'skills',
        'expected_salary',
        'profile_summary',
        'profile_image',
        'phone',
        'email',
        'resume_url',
        'status',
    ];

    protected $casts = [
        'experience_years' => 'integer',
    ];

    public function scopeDiscoverable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * @return list<string>
     */
    public function skillsList(): array
    {
        if (! filled($this->skills)) {
            return [];
        }

        if (is_string($this->skills)) {
            $decoded = json_decode($this->skills, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }

            return array_values(array_filter(array_map('trim', explode(',', $this->skills))));
        }

        return [];
    }
}
