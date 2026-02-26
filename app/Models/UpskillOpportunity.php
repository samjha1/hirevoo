<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpskillOpportunity extends Model
{
    protected $fillable = [
        'title',
        'company_name',
        'description',
        'skills',
        'cta_type',
        'cta_label',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get skills as array (empty if null or not array).
     * Cleans escaped slashes so "C++ \/ Java" displays as "C++ / Java".
     */
    public function getSkillsList(): array
    {
        $skills = $this->skills;
        if (! is_array($skills)) {
            return [];
        }
        $list = array_values(array_filter(array_map(function ($s) {
            $s = is_string($s) ? trim($s) : '';
            return str_replace('\/', '/', $s);
        }, $skills)));
        return array_filter($list);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
