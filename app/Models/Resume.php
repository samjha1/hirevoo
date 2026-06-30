<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'file_path',
        'file_name',
        'mime_type',
        'ai_score',
        'ai_score_explanation',
        'ai_summary',
        'extracted_skills',
        'is_primary',
    ];

    protected $casts = [
        'extracted_skills' => 'array',
        'is_primary' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * storeUploadedFile() returns a full S3 URL or a local path (resumes/…).
     */
    public function setFilePathAttribute(?string $value): void
    {
        $this->attributes['file_path'] = $value;
    }

    /**
     * Get extracted skills as a lowercase array for matching.
     */
    public function getExtractedSkillsList(): array
    {
        $skills = $this->extracted_skills;
        if (! is_array($skills)) {
            return [];
        }
        $list = array_filter(array_map(function ($s) {
            return is_string($s) ? strtolower(trim($s)) : '';
        }, $skills));
        return array_values(array_unique($list));
    }
}
