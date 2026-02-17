<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobRole extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'is_active'];

    public function requiredSkills(): HasMany
    {
        return $this->hasMany(JobRequiredSkill::class);
    }
}
