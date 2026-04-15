<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferrerProfile extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'company_email', 'company_email_verified',
        'referral_code',
        'designation', 'department', 'profile_photo',
        'gstin', 'gst_verified', 'company_legal_name', 'company_address', 'invoice_consent',
        'is_approved', 'approved_at', 'credits',
    ];

    protected function casts(): array
    {
        return [
            'company_email_verified' => 'boolean',
            'gst_verified' => 'boolean',
            'invoice_consent' => 'boolean',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
