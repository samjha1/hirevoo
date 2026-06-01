<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerTalentPoolAction extends Model
{
    public const SOURCE_VERIFIED = 'verified';

    public const SOURCE_TALENT_POOL = 'talent_pool';

    protected $fillable = [
        'employer_user_id',
        'candidate_source',
        'candidate_ref_id',
        'is_saved',
        'is_shortlisted',
    ];

    protected $casts = [
        'is_saved' => 'boolean',
        'is_shortlisted' => 'boolean',
    ];

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_user_id');
    }

    public static function validSource(string $source): bool
    {
        return in_array($source, [self::SOURCE_VERIFIED, self::SOURCE_TALENT_POOL], true);
    }
}
