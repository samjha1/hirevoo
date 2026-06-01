<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidEmployerReferralCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        if (! Schema::hasTable('admins') || ! Schema::hasColumn('admins', 'referral_code')) {
            $fail('Referral codes are not available right now.');

            return;
        }

        $exists = DB::table('admins')
            ->whereRaw('UPPER(referral_code) = ?', [strtoupper(trim((string) $value))])
            ->where('sales_team', 'employer')
            ->whereIn('role', ['sales_manager', 'sales_employee'])
            ->exists();

        if (! $exists) {
            $fail('This referral code is not valid.');
        }
    }
}
