<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerTalentPoolUnlock extends Model
{
    protected $fillable = [
        'employer_user_id',
        'candidate_source',
        'candidate_ref_id',
        'credits_spent',
    ];

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_user_id');
    }
}
