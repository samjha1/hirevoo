<?php

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use App\Services\CandidateProfileFillerFromResume;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected CandidateProfileFillerFromResume $profileFiller
    ) {}

    public function show(): View
    {
        $user = auth()->user();
        $profile = $user->candidateProfile;
        if ($user->isCandidate()) {
            $profile = CandidateProfile::firstOrCreate(
                ['user_id' => $user->id],
                []
            );
        }
        $hasResume = $user->isCandidate() && $user->resumes()->exists();
        $latestResume = $user->isCandidate() ? $user->resumes()->orderByDesc('created_at')->first() : null;

        return view('hirevo.profile', [
            'profile' => $profile,
            'user' => $user,
            'hasResume' => $hasResume,
            'latestResume' => $latestResume,
            'profileOnboardingComplete' => $user->isCandidate() && $user->candidate_profile_completed_at,
            'profileCompletion' => $user->isCandidate()
                ? CandidateProfile::completionStats($profile, $user)
                : ['percent' => 0, 'filled' => 0, 'total' => 0],
            'profileSectionsDone' => $user->isCandidate()
                ? CandidateProfile::sectionCompletionFlags($profile, $user)
                : [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isCandidate()) {
            return redirect()->route('profile')->with('info', 'Profile update is for candidates.');
        }

        $mustComplete = ! $user->candidate_profile_completed_at;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'headline' => [$mustComplete ? 'required' : 'nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'education' => [$mustComplete ? 'required' : 'nullable', 'string', 'max:500'],
            'experience_years' => [$mustComplete ? 'required' : 'nullable', 'integer', 'min:0', 'max:50'],
            'experience_months' => ['nullable', 'integer', 'min:0', 'max:11'],
            'skills' => [$mustComplete ? 'required' : 'nullable', 'string', 'max:4000'],
            'tools' => ['nullable', 'string', 'max:2000'],
            'technical_skill_level' => ['nullable', 'in:Beginner,Intermediate,Expert'],
            'location' => [$mustComplete ? 'required' : 'nullable', 'string', 'max:255'],
            'bio_summary' => ['nullable', 'string', 'max:5000'],
            'career_objective' => ['nullable', 'string', 'max:2000'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Male,Female,Other,Prefer not to say'],
            'current_company' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:500'],
            'github_url' => ['nullable', 'string', 'max:500'],
            'portfolio_url' => ['nullable', 'string', 'max:500'],
            'preferred_job_location' => ['nullable', 'string', 'max:255'],
            'preferred_job_role' => ['nullable', 'string', 'max:255'],
            'job_type' => ['nullable', 'in:Full-time,Part-time,Remote,Hybrid,Contract,Internship'],
            'notice_period' => ['nullable', 'string', 'max:64'],
            'expected_salary' => ['nullable', 'string', 'max:100'],
            'expected_salary_currency' => ['nullable', 'string', 'max:8'],
            'expected_salary_period' => ['nullable', 'in:per_month,per_annum'],
            'current_salary' => ['nullable', 'string', 'max:120'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'work_experience' => ['nullable', 'array', 'max:10'],
            'work_experience.*.company' => ['nullable', 'string', 'max:255'],
            'work_experience.*.role' => ['nullable', 'string', 'max:255'],
            'work_experience.*.start_date' => ['nullable', 'string', 'max:64'],
            'work_experience.*.end_date' => ['nullable', 'string', 'max:64'],
            'work_experience.*.current' => ['nullable', 'boolean'],
            'work_experience.*.description' => ['nullable', 'string', 'max:2000'],
            'education_history' => ['nullable', 'array', 'max:8'],
            'education_history.*.degree' => ['nullable', 'string', 'max:120'],
            'education_history.*.institution' => ['nullable', 'string', 'max:255'],
            'education_history.*.field' => ['nullable', 'string', 'max:255'],
            'education_history.*.start_year' => ['nullable', 'string', 'max:32'],
            'education_history.*.end_year' => ['nullable', 'string', 'max:32'],
            'education_history.*.grade' => ['nullable', 'string', 'max:64'],
            'projects' => ['nullable', 'array', 'max:8'],
            'projects.*.title' => ['nullable', 'string', 'max:255'],
            'projects.*.description' => ['nullable', 'string', 'max:2000'],
            'projects.*.technologies' => ['nullable', 'string', 'max:500'],
            'projects.*.link' => ['nullable', 'string', 'max:500'],
            'certifications' => ['nullable', 'array', 'max:12'],
            'certifications.*.name' => ['nullable', 'string', 'max:255'],
            'certifications.*.issued_by' => ['nullable', 'string', 'max:255'],
            'certifications.*.year' => ['nullable', 'string', 'max:32'],
            'certifications.*.link' => ['nullable', 'string', 'max:500'],
        ];

        $validated = $request->validate($rules, [
            'headline.required' => 'Add a professional headline (job title) to continue.',
            'phone.required' => 'Add your phone number to continue.',
            'education.required' => 'Add your education summary to continue.',
            'experience_years.required' => 'Add years of experience (use 0 if fresher).',
            'skills.required' => 'Add at least one skill to continue.',
            'location.required' => 'Add your location to continue.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        $existing = $user->candidateProfile;

        $attrs = [
            'headline' => $validated['headline'] ?? null,
            'bio_summary' => $validated['bio_summary'] ?? null,
            'career_objective' => $validated['career_objective'] ?? null,
            'education' => $validated['education'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'tools' => $validated['tools'] ?? null,
            'technical_skill_level' => $validated['technical_skill_level'] ?? null,
            'location' => $validated['location'] ?? null,
            'current_company' => $validated['current_company'] ?? null,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'github_url' => $validated['github_url'] ?? null,
            'portfolio_url' => $validated['portfolio_url'] ?? null,
            'preferred_job_location' => $validated['preferred_job_location'] ?? null,
            'preferred_job_role' => $validated['preferred_job_role'] ?? null,
            'job_type' => $validated['job_type'] ?? null,
            'notice_period' => $validated['notice_period'] ?? null,
            'expected_salary' => $validated['expected_salary'] ?? null,
            'expected_salary_currency' => $validated['expected_salary_currency'] ?? null,
            'expected_salary_period' => $validated['expected_salary_period'] ?? null,
            'current_salary' => $validated['current_salary'] ?? null,
            'experience_years' => array_key_exists('experience_years', $validated) && $validated['experience_years'] !== null && $validated['experience_years'] !== ''
                ? (int) $validated['experience_years']
                : null,
            'experience_months' => array_key_exists('experience_months', $validated)
                ? ($validated['experience_months'] !== null && $validated['experience_months'] !== ''
                    ? (int) $validated['experience_months']
                    : null)
                : null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'work_experience' => $this->filterListRows($validated['work_experience'] ?? [], [
                'company', 'role', 'start_date', 'end_date', 'description',
            ], 'current'),
            'education_history' => $this->filterListRows($validated['education_history'] ?? [], [
                'degree', 'institution', 'field', 'start_year', 'end_year', 'grade',
            ]),
            'projects' => $this->filterListRows($validated['projects'] ?? [], [
                'title', 'description', 'technologies', 'link',
            ]),
            'certifications' => $this->filterListRows($validated['certifications'] ?? [], [
                'name', 'issued_by', 'year', 'link',
            ]),
        ];

        if ($request->hasFile('profile_photo')) {
            $newPath = $request->file('profile_photo')->store('profile-photos', 'public');
            if ($existing?->profile_photo_path) {
                Storage::disk('public')->delete($existing->profile_photo_path);
            }
            $attrs['profile_photo_path'] = $newPath;
        } elseif ($existing) {
            $attrs['profile_photo_path'] = $existing->profile_photo_path;
        }

        $user->candidateProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $attrs
        );

        $user->refresh();
        $user->syncCandidateProfileCompletion();

        $msg = $user->candidate_profile_completed_at
            ? 'Profile saved. Your details are used for job matching and applications.'
            : 'Profile saved. Complete all required fields and upload your resume to continue.';

        return redirect()->route('profile')->with('success', $msg);
    }

    /**
     * @param  list<array<string, mixed>>|null  $rows
     * @param  list<string>  $textKeys
     * @return list<array<string, mixed>>|null
     */
    protected function filterListRows(?array $rows, array $textKeys, ?string $boolKey = null): ?array
    {
        if ($rows === null || $rows === []) {
            return null;
        }
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $item = [];
            foreach ($textKeys as $k) {
                $item[$k] = isset($row[$k]) ? trim((string) $row[$k]) : '';
            }
            if ($boolKey !== null) {
                $item[$boolKey] = ! empty($row[$boolKey]);
            }
            $flat = implode('', array_map(fn ($v) => is_bool($v) ? '' : (string) $v, $item));
            if (trim($flat) !== '') {
                $out[] = $item;
            }
        }

        return $out === [] ? null : $out;
    }

    public function fillFromResume(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isCandidate()) {
            return redirect()->route('profile')->with('info', 'This feature is for candidates.');
        }

        if (! $user->resumes()->exists()) {
            return redirect()->route('profile')->with('info', 'Upload a resume first, then use “Fill from resume”.');
        }

        $usedGpt = $this->profileFiller->fill($user);

        return redirect()->route('profile')->with(
            'success',
            $usedGpt
                ? 'Profile fields were filled from your resume (AI). Review every section and click Save profile.'
                : 'Profile was updated from your resume (skills & headline). Turn on AI for fuller extraction, or edit manually below.'
        );
    }
}
