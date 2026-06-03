<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

class EmployerVerification
{
    /**
     * Unapproved employers must complete email OTP before using the dashboard.
     */
    public static function redirectIfPending(User $user): ?RedirectResponse
    {
        if (! $user->isReferrer()) {
            return null;
        }

        $profile = $user->referrerProfile;
        if (! $profile || $profile->is_approved) {
            return null;
        }

        session(['verify_email_auto_send' => true]);

        return redirect()
            ->route('verify-email')
            ->with('info', 'Please verify your email. We will send a one-time password (OTP) to your inbox.');
    }
}
