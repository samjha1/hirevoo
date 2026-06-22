<?php

namespace App\Support;

use App\Models\User;

class RoleEmailGuard
{
    /**
     * Block when the same email is already used for the opposite account type (candidate vs employer).
     */
    public static function crossRoleConflict(string $intendedRole, User $existing): ?string
    {
        if ($intendedRole === 'referrer' && ! $existing->isReferrer()) {
            return 'This email is already registered as a candidate. Please sign in as a candidate or use a different email for employer sign up.';
        }

        if ($intendedRole !== 'referrer' && $existing->isReferrer()) {
            return 'This email is already registered as an employer. Please use For Employers → Log in as employer, or use a different email.';
        }

        return null;
    }

    /**
     * Validation message when registering with an email that already exists.
     */
    public static function registrationError(string $intendedRole, string $email): ?string
    {
        $existing = User::where('email', strtolower(trim($email)))->first();
        if (! $existing) {
            return null;
        }

        if ($message = self::crossRoleConflict($intendedRole, $existing)) {
            return $message;
        }

        return 'This email is already registered. Please sign in instead.';
    }
}
