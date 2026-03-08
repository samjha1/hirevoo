<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralFormSubmission extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'source',
    ];
}
