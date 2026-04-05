<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCandidateOnboardingComplete
{
    /**
     * Candidates must complete profile, then upload a resume, before using the rest of the app.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User || ! $user->isCandidate()) {
            return $next($request);
        }

        if ($this->routeIsExempt($request)) {
            return $next($request);
        }

        if (! $user->candidate_profile_completed_at) {
            return redirect()
                ->route('profile')
                ->with('info', 'Welcome! Complete your profile first — then you can upload your resume and use the rest of Hirevo.');
        }

        if (! $user->resumes()->exists()) {
            return redirect()
                ->route('profile')
                ->with('info', 'Upload your resume on your profile — we’ll pull your details into the form so you can review and save.');
        }

        return $next($request);
    }

    protected function routeIsExempt(Request $request): bool
    {
        return $request->routeIs([
            'profile',
            'profile.update',
            'profile.fill-from-resume',
            'resume.upload',
            'resume.upload.store',
            'resume.results',
            'resume.lead',
            'notifications.read-all',
            'notifications.read',
        ]);
    }
}
