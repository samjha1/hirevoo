<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyReferrerSignup extends Model
{
    protected $table = 'company_referrer_signups';

    protected $fillable = [
        'company_name',
        'name',
        'email',
        'phone',
        'max_candidates',
        'message',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'max_candidates' => 'integer',
        ];
    }
}
