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
}
