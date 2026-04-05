<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class CandidateOnboarding
{
    /**
     * Redirect candidates who have not finished profile + resume (for routes outside onboarding middleware).
     */
    public static function redirectIfIncomplete(User $user): ?RedirectResponse
    {
        if (! $user->isCandidate()) {
            return null;
        }

        if (! $user->candidate_profile_completed_at) {
            return redirect()
                ->route('profile')
                ->with('info', 'Complete your profile first, then upload your resume to continue.');
        }

        if (! $user->resumes()->exists()) {
            return redirect()
                ->route('resume.upload')
                ->with('info', 'Upload your resume to continue.');
        }

        return null;
    }
}
