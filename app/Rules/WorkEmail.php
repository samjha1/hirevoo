<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WorkEmail implements ValidationRule
{
    /** Blocked free/personal email domains for employer sign-up */
    protected static array $freeDomains = [
        'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.in', 'hotmail.com',
        'outlook.com', 'live.com', 'msn.com', 'icloud.com', 'me.com', 'mail.com',
        'protonmail.com', 'zoho.com', 'yandex.com', 'rediffmail.com', 'aol.com',
        'gmail.com', 'google.com', 'inbox.com', 'mailinator.com', 'tempmail.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $at = strrchr($value, '@');
        $domain = $at ? strtolower((string) substr($at, 1)) : '';

        if ($domain === '' || in_array($domain, self::$freeDomains, true)) {
            $fail($domain === '' ? 'Please enter a valid work email.' : 'Please use your company work email. Personal email domains (e.g. Gmail, Yahoo) are not allowed for employer sign-up.');
        }
    }
}
