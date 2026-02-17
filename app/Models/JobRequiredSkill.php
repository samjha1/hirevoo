<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRequiredSkill extends Model
{
    protected $fillable = ['job_role_id', 'skill_name', 'priority'];

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class);
    }
}
