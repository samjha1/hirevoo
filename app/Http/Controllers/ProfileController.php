<?php

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use App\Services\GptService;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected GptService $gptService,
        protected ResumeAnalysisService $resumeAnalysis
    ) {}

    public function show(): View
    {
        $user = auth()->user();
        $profile = $user->candidateProfile;
        $hasResume = $user->isCandidate() && $user->resumes()->exists();
        $latestResume = $user->isCandidate() ? $user->resumes()->orderByDesc('created_at')->first() : null;

        return view('hirevo.profile', [
            'profile' => $profile,
            'user' => $user,
            'hasResume' => $hasResume,
            'latestResume' => $latestResume,
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
            'phone' => ['nullable', 'string', 'max:20'],
            'education' => ['nullable', 'string', 'max:500'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'expected_salary' => ['nullable', 'string', 'max:100'],
        ]);

        if ($request->filled('phone')) {
            $user->update(['phone' => $validated['phone']]);
        }

        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
        $profile->headline = $validated['headline'] ?? $profile->headline;
        $profile->education = $validated['education'] ?? $profile->education;
        $profile->experience_years = isset($validated['experience_years']) ? (int) $validated['experience_years'] : $profile->experience_years;
        $profile->skills = $validated['skills'] ?? $profile->skills;
        $profile->location = $validated['location'] ?? $profile->location;
        $profile->expected_salary = $validated['expected_salary'] ?? $profile->expected_salary;
        $profile->save();

        return redirect()->route('profile')->with('success', 'Profile updated. Your skills and details are used for job matching.');
    }

    /**
     * Fill candidate profile from latest resume using AI (ChatGPT). Redirects back to profile.
     */
    public function fillFromResume(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isCandidate()) {
            return redirect()->route('profile')->with('info', 'This feature is for candidates.');
        }

        $resume = $user->resumes()->orderByDesc('created_at')->first();
        if (! $resume) {
            return redirect()->route('profile')->with('info', 'Upload a resume first, then use "Fill from resume".');
        }

        $path = storage_path('app/' . $resume->file_path);
        $text = $this->resumeAnalysis->extractTextFromFile($path, $resume->mime_type ?? 'application/pdf');

        if ($this->gptService->isAvailable() && trim($text) !== '') {
            $data = $this->gptService->extractProfileFromResume($text);
            if ($data) {
                if (! empty($data['phone'])) {
                    $user->update(['phone' => $data['phone']]);
                }
                $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
                if (! empty($data['headline'])) {
                    $profile->headline = $data['headline'];
                }
                if (! empty($data['education'])) {
                    $profile->education = $data['education'];
                }
                if (isset($data['experience_years']) && $data['experience_years'] !== null) {
                    $profile->experience_years = $data['experience_years'];
                }
                if (! empty($data['skills'])) {
                    $profile->skills = $data['skills'];
                }
                if (! empty($data['location'])) {
                    $profile->location = $data['location'];
                }
                if (! empty($data['expected_salary'])) {
                    $profile->expected_salary = $data['expected_salary'];
                }
                $profile->save();
                return redirect()->route('profile')->with('success', 'Profile filled from your resume using AI. Review and save any changes.');
            }
        }

        $this->resumeAnalysis->fillProfileFromResumeFallback($resume);
        return redirect()->route('profile')->with('success', 'Profile updated from your resume (skills and headline). You can edit any field below.');
    }
}
