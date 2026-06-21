<?php

namespace App\Models;

use App\Support\StoredFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferrerProfile extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'company_email', 'company_email_verified',
        'referral_code',
        'designation', 'department', 'profile_photo',
        'gstin', 'gst_verified', 'company_legal_name', 'company_address', 'invoice_consent',
        'is_approved', 'approved_at', 'credits', 'talent_pool_tokens',
        'subscription_plan', 'subscription_started_at', 'subscription_expires_at',
    ];

    protected $casts = [
        'company_email_verified' => 'boolean',
        'gst_verified' => 'boolean',
        'invoice_consent' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'subscription_started_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'talent_pool_tokens' => 'integer',
    ];

    public function setReferralCodeAttribute(?string $value): void
    {
        $trimmed = $value !== null ? trim($value) : '';
        $this->attributes['referral_code'] = $trimmed !== '' ? strtoupper($trimmed) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setProfilePhotoAttribute(?string $value): void
    {
        if ($value !== null && ! StoredFile::isAbsoluteUrl($value) && ! str_starts_with($value, 'uploads/')) {
            $value = StoredFile::databaseValueFromStoragePath($value);
        }

        $this->attributes['profile_photo'] = $value;
    }

    public function profilePhotoUrl(): ?string
    {
        if (! filled($this->profile_photo)) {
            return null;
        }

        if (str_starts_with($this->profile_photo, 'uploads/')) {
            return asset($this->profile_photo);
        }

        // Private S3 bucket: raw URLs return Access Denied — use a presigned URL for <img src>.
        if (StoredFile::uploadsDisk() === 's3' && StoredFile::exists($this->profile_photo)) {
            return StoredFile::signedUrl($this->profile_photo)
                ?? route('employer.profile.photo', ['v' => $this->updated_at?->getTimestamp() ?? 0]);
        }

        return route('employer.profile.photo', [
            'v' => $this->updated_at?->getTimestamp() ?? 0,
        ]);
    }
}
