<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployerProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isReferrer()) {
            return $next($request);
        }

        $profile = $user->referrerProfile;
        if (! $profile || ! $this->isProfileComplete($profile)) {
            return redirect()
                ->route('employer.profile')
                ->with('info', 'Please complete your company profile to continue.');
        }

        // Check if email is verified
        if (! $profile->is_approved) {
            return redirect()
                ->route('verify-email')
                ->with('info', 'Please verify your email to continue.');
        }

        return $next($request);
    }

    private function isProfileComplete($profile): bool
    {
        return ! empty($profile->company_name) && ! empty($profile->company_email);
    }
}
