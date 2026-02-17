<?php

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $profile = $user->candidateProfile;

        return view('hirevo.profile', [
            'profile' => $profile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isCandidate()) {
            return redirect()->route('profile')->with('info', 'Profile update is for candidates.');
        }

        $validated = $request->validate([
            'headline' => ['nullable', 'string', 'max:255'],
            'skills'   => ['nullable', 'string', 'max:2000'],
        ]);

        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
        $profile->headline = $validated['headline'] ?? $profile->headline;
        $profile->skills = $validated['skills'] ?? $profile->skills;
        $profile->save();

        return redirect()->route('profile')->with('success', 'Profile updated. Your skills are used for skill match on Job Goals.');
    }
}
