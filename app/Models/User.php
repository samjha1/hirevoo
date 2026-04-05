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
