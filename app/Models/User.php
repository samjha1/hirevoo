<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'candidate_profile_completed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'candidate_profile_completed_at' => 'datetime',
        ];
    }

    public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function referrerProfile()
    {
        return $this->hasOne(ReferrerProfile::class);
    }

    public function edtechProfile()
    {
        return $this->hasOne(EdtechProfile::class);
    }

    public function resumes()
    {
        return $this->hasMany(Resume::class);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function employerJobApplications()
    {
        return $this->hasMany(EmployerJobApplication::class);
    }

    public function employerJobs()
    {
        return $this->hasMany(EmployerJob::class, 'user_id');
    }

    public function isCandidate(): bool
    {
        return $this->role === 'candidate';
    }

    public function isReferrer(): bool
    {
        return $this->role === 'referrer';
    }

    public function isEdtech(): bool
    {
        return $this->role === 'edtech';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Two-letter initials for nav avatars (first + last word, or first two letters of a single word).
     */
    public function initials(): string
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) >= 2) {
            $a = mb_substr($parts[0], 0, 1);
            $b = mb_substr($parts[count($parts) - 1], 0, 1);

            return mb_strtoupper($a.$b, 'UTF-8');
        }

        $single = $parts[0] ?? $name;
        $len = mb_strlen($single);
        if ($len <= 1) {
            return mb_strtoupper($single, 'UTF-8');
        }

        return mb_strtoupper(mb_substr($single, 0, 2), 'UTF-8');
    }

    /**
     * Set candidate_profile_completed_at when required profile fields are present (or clear when not).
     */
    public function syncCandidateProfileCompletion(): void
    {
        if (! $this->isCandidate()) {
            return;
        }

        $this->load('candidateProfile');
        $p = $this->candidateProfile;
        if (! $p) {
            $this->forceFill(['candidate_profile_completed_at' => null])->saveQuietly();

            return;
        }

        $complete = $this->phone
            && $p->headline
            && $p->skills
            && $p->location
            && $p->education
            && $p->experience_years !== null;

        if ($complete && ! $this->candidate_profile_completed_at) {
            $this->forceFill(['candidate_profile_completed_at' => now()])->saveQuietly();

            return;
        }

        if (! $complete && $this->candidate_profile_completed_at) {
            $this->forceFill(['candidate_profile_completed_at' => null])->saveQuietly();
        }
    }
}
