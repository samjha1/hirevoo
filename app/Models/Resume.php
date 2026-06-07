<?php

namespace App\Models;

use App\Support\StoredFile;
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
     * New uploads: full AWS URL. Legacy Hostinger rows keep resumes/… paths unchanged.
     */
    public function setFilePathAttribute(?string $value): void
    {
        if ($value !== null
            && ! StoredFile::isAbsoluteUrl($value)
            && ! StoredFile::isLegacyLocalPath($value)) {
            $value = StoredFile::databaseValueFromStoragePath($value);
        }

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
