<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferrerProfile extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'company_email', 'company_email_verified',
        'designation', 'department', 'is_approved', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'company_email_verified' => 'boolean',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
