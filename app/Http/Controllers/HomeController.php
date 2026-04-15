<?php

namespace App\Http\Controllers;

use App\Models\CareerConsultationRequest;
use App\Models\CandidateProfile;
use App\Models\EmployerJob;
use App\Models\EmployerJobApplication;
use App\Models\JobApplication;
use App\Models\JobRole;
use App\Models\Resume;
use App\Models\UpskillOpportunity;
use App\Services\GptService;
use App\Services\ResumeAnalysisService;
use App\Support\CandidateOnboarding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $jobRoles = JobRole::where('is_active', true)
            ->withCount('requiredSkills')
            ->orderBy('title')
            ->limit(8)
            ->get();
        return view('hirevo.index', compact('jobRoles'));
    }

    public function jobList(Request $request): View
    {
        $query = JobRole::where('is_active', true);

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            });
        }

        $jobRoles = $query->orderBy('title')->get();
        $appliedJobIds = auth()->check()
            ? \App\Models\JobApplication::where('user_id', auth()->id())->pluck('job_role_id')->all()
            : [];
        $searchQuery = $request->get('q', '');
        $searchLocation = $request->get('location', '');

        return view('hirevo.job-list', compact('jobRoles', 'appliedJobIds', 'searchQuery', 'searchLocation'));
    }

    public function skillMatch(JobRole $jobRole, ResumeAnalysisService $resumeAnalysis): View
    {
        $jobRole->load('requiredSkills');
        $orderedSkillLabels = $jobRole->requiredSkills->isNotEmpty()
            ? $resumeAnalysis->orderedRequiredSkillLabels($jobRole)
            : [];

        $matchPercentage = 0;
        $matchedSkills = [];
        $missingSkills = $orderedSkillLabels;
        $candidateSkills = [];

        $primaryResume = null;
        $matchingJobGoals = [];
        $upskillOpportunities = collect();
        $appliedJobIds = [];
        $userSkillsForUpskill = [];

        if (auth()->check() && auth()->user()->isCandidate()) {
            $appliedJobIds = JobApplication::where('user_id', auth()->id())->pluck('job_role_id')->all();
            $primaryResume = auth()->user()->resumes()->where('is_primary', true)->first()
                ?? auth()->user()->resumes()->orderByDesc('created_at')->first();
            if ($primaryResume) {
                $matchingJobGoals = $resumeAnalysis->getMatchingJobGoalsForResume($primaryResume, 20);
                $upskillOpportunities = UpskillOpportunity::active()->orderBy('sort_order')->get();
                $userSkillsForUpskill = array_map('strtolower', $primaryResume->getExtractedSkillsList());
            }

            if ($primaryResume && count($orderedSkillLabels) > 0) {
                $cov = $resumeAnalysis->matchResumeToRequiredSkillNames(
                    $primaryResume,
                    $orderedSkillLabels,
                    auth()->user()->candidateProfile?->skills
                );
                $matchPercentage = $cov['match_pct'];
                $matchedSkills = $cov['matched_display'];
                $missingSkills = $cov['gaps_display'];
                $candidateSkills = array_map('strtolower', $primaryResume->getExtractedSkillsList());
            } else {
                $profile = auth()->user()->candidateProfile;
                if ($profile && trim((string) $profile->skills) !== '' && count($orderedSkillLabels) > 0) {
                    $skillSet = [];
                    foreach (preg_split('/[\s,;|]+/', $profile->skills, -1, PREG_SPLIT_NO_EMPTY) as $p) {
                        $skillSet[mb_strtolower(trim($p))] = true;
                    }
                    $matchedSkills = [];
                    $missingSkills = [];
                    foreach ($orderedSkillLabels as $label) {
                        if (isset($skillSet[mb_strtolower($label)])) {
                            $matchedSkills[] = $label;
                        } else {
                            $missingSkills[] = $label;
                        }
                    }
                    $matchPercentage = (int) round((count($matchedSkills) / count($orderedSkillLabels)) * 100);
                    $candidateSkills = array_keys($skillSet);
                } elseif (count($orderedSkillLabels) > 0) {
                    $missingSkills = $orderedSkillLabels;
                    $matchedSkills = [];
                    $matchPercentage = 0;
                    $candidateSkills = [];
                }
            }
        }

        $consultGapPayload = CareerConsultationRequest::buildConsultGapPayload(
            $jobRole,
            $missingSkills,
            $matchedSkills
        );

        $hasApplied = auth()->check()
            ? JobApplication::where('user_id', auth()->id())->where('job_role_id', $jobRole->id)->exists()
            : false;

        // Related employer job openings: match by job role title and required skills keywords
        $keywords = array_merge(
            array_filter(explode(' ', preg_replace('/[^a-z0-9\s]/i', ' ', $jobRole->title))),
            $jobRole->requiredSkills->pluck('skill_name')->take(5)->map(fn ($s) => trim($s))->all()
        );
        $keywords = array_values(array_unique(array_filter(array_map('strtolower', $keywords), fn ($kw) => strlen($kw) >= 2)));
        $relatedJobs = collect();
        if (count($keywords) > 0) {
            $query = EmployerJob::where('status', 'active')->with('user');
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('title', 'like', '%' . $kw . '%')
                        ->orWhere('description', 'like', '%' . $kw . '%');
                }
            });
            $relatedJobs = $query->orderByDesc('created_at')->limit(10)->get();
        }
        $appliedEmployerJobIds = auth()->check()
            ? EmployerJobApplication::where('user_id', auth()->id())->pluck('employer_job_id')->all()
            : [];

        return view('hirevo.skill-match', [
            'jobRole' => $jobRole,
            'requiredSkills' => $jobRole->requiredSkills,
            'matchPercentage' => $matchPercentage,
            'matchedSkills' => $matchedSkills,
            'missingSkills' => $missingSkills,
            'candidateSkills' => $candidateSkills,
            'hasProfile' => auth()->check() && auth()->user()->candidateProfile,
            'hasApplied' => $hasApplied,
            'primaryResume' => $primaryResume,
            'matchingJobGoals' => $matchingJobGoals,
            'upskillOpportunities' => $upskillOpportunities,
            'appliedJobIds' => $appliedJobIds,
            'userSkillsForUpskill' => $userSkillsForUpskill,
            'relatedJobs' => $relatedJobs,
            'appliedEmployerJobIds' => $appliedEmployerJobIds,
            'consultGapPayload' => $consultGapPayload,
        ]);
    }

    public function pricing(): View
    {
        return view('hirevo.pricing');
    }

    public function jobOpenings(Request $request, ResumeAnalysisService $resumeAnalysis, GptService $gptService): View|JsonResponse|RedirectResponse
    {
        if ($request->boolean('clear_personalization')) {
            $request->session()->forget(['job_openings_personalize_resume_id', 'job_openings_personalized']);

            return redirect()->route('job-openings', $request->except('clear_personalization'));
        }

        $query = EmployerJob::where('status', 'active')->with(['user.referrerProfile']);

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhereHas('user.referrerProfile', function ($uq) use ($q) {
                        $uq->where('company_name', 'like', '%' . $q . '%');
                    });
            });
        }

        $validCountryCodes = ['ca', 'us', 'gb', 'ae'];
        $countryFilter = '';
        if ($request->filled('country')) {
            $c = strtolower((string) $request->get('country'));
            if (in_array($c, $validCountryCodes, true)) {
                $countryFilter = $c;
            }
        }

        $countryHints = config('hirevo.job_openings_country_hints', []);
        if ($countryFilter !== '' && isset($countryHints[$countryFilter]) && is_array($countryHints[$countryFilter])) {
            $terms = array_values(array_filter(
                $countryHints[$countryFilter],
                fn ($t) => is_string($t) && trim($t) !== ''
            ));
            if ($terms !== []) {
                $query->where(function ($qry) use ($terms) {
                    foreach ($terms as $i => $term) {
                        if ($i === 0) {
                            $qry->where('location', 'like', '%' . $term . '%');
                        } else {
                            $qry->orWhere('location', 'like', '%' . $term . '%');
                        }
                    }
                });
            }
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->get('location') . '%');
        }

        $validJobTypes = ['full_time', 'part_time', 'contract', 'internship', 'temporary', 'volunteer', 'other'];
        if ($request->filled('job_type') && in_array($request->get('job_type'), $validJobTypes, true)) {
            $query->where('job_type', $request->get('job_type'));
        }

        $validWorkTypes = ['office', 'remote', 'hybrid'];
        if ($request->filled('work_location_type') && in_array($request->get('work_location_type'), $validWorkTypes, true)) {
            $query->where('work_location_type', $request->get('work_location_type'));
        }

        $appliedIds = auth()->check()
            ? EmployerJobApplication::where('user_id', auth()->id())->pluck('employer_job_id')->all()
            : [];

        if (auth()->check() && auth()->user()->isCandidate() && $appliedIds !== []) {
            $query->whereNotIn('id', $appliedIds);
        }

        $searchQuery = $request->get('q', '');
        $searchLocation = $request->get('location', '');
        $filterJobType = $request->get('job_type', '');
        $filterWorkType = $request->get('work_location_type', '');

        $filterHash = md5($searchQuery.'|'.$searchLocation.'|'.$filterJobType.'|'.$filterWorkType.'|'.$countryFilter);

        $jobMatchScores = [];
        $jobMatchAiRanked = false;
        $jobsPersonalized = false;
        $personalizeResumeId = null;

        if (auth()->check() && auth()->user()->isCandidate()) {
            $personalizeResumeId = (int) $request->session()->get('job_openings_personalize_resume_id', 0);
            if ($personalizeResumeId > 0) {
                $owns = Resume::where('user_id', auth()->id())->where('id', $personalizeResumeId)->exists();
                if (! $owns) {
                    $request->session()->forget(['job_openings_personalize_resume_id', 'job_openings_personalized']);
                    $personalizeResumeId = null;
                }
            } else {
                $personalizeResumeId = null;
            }
        }

        $jobs = null;
        if ($personalizeResumeId) {
            $resume = Resume::where('user_id', auth()->id())->find($personalizeResumeId);
            if (! $resume) {
                $request->session()->forget(['job_openings_personalize_resume_id', 'job_openings_personalized']);
            } elseif ($resume) {
                $cached = $request->session()->get('job_openings_personalized');
                if (is_array($cached)
                    && (int) ($cached['resume_id'] ?? 0) === (int) $resume->id
                    && ($cached['filter_hash'] ?? '') === $filterHash
                    && isset($cached['ordered_ids']) && is_array($cached['ordered_ids'])) {
                    $orderedIds = $cached['ordered_ids'];
                    $jobMatchScores = is_array($cached['scores'] ?? null) ? $cached['scores'] : [];
                    $jobMatchAiRanked = (bool) ($cached['ai_ranked'] ?? false);
                } else {
                    $pool = (clone $query)->orderByDesc('created_at')->limit(120)->get();
                    $ordered = $resumeAnalysis->orderEmployerJobsForOpeningsList($resume, $pool, $gptService);
                    $orderedIds = $ordered['ordered_ids'];
                    $jobMatchScores = $ordered['scores'];
                    $jobMatchAiRanked = $ordered['ai_ranked'];
                    $request->session()->put('job_openings_personalized', [
                        'resume_id' => $resume->id,
                        'filter_hash' => $filterHash,
                        'ordered_ids' => $orderedIds,
                        'scores' => $jobMatchScores,
                        'ai_ranked' => $jobMatchAiRanked,
                    ]);
                }

                $total = count($orderedIds);
                $perPage = 10;
                $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
                $currentPage = min($lastPage, max(1, (int) $request->get('page', 1)));
                $offset = ($currentPage - 1) * $perPage;
                $pageIds = array_slice($orderedIds, $offset, $perPage);

                $pageItems = [];
                if ($pageIds !== []) {
                    $jobsMap = EmployerJob::whereIn('id', $pageIds)
                        ->with(['user.referrerProfile'])
                        ->get()
                        ->keyBy('id');

                    foreach ($pageIds as $jid) {
                        $jid = (int) $jid;
                        if ($jobsMap->has($jid)) {
                            $pageItems[] = $jobsMap->get($jid);
                        }
                    }
                }

                $jobs = new LengthAwarePaginator(
                    $pageItems,
                    $total,
                    $perPage,
                    $currentPage,
                    [
                        'path' => $request->url(),
                        'pageName' => 'page',
                    ]
                );
                $jobs->withQueryString();
                $jobsPersonalized = true;
            }
        }

        if ($jobs === null) {
            $jobs = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        }

        $locationOptions = EmployerJob::where('status', 'active')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->sort()
            ->values()
            ->all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('hirevo.partials.employer-job-cards', [
                    'jobs' => $jobs,
                    'appliedIds' => $appliedIds,
                    'jobMatchScores' => $jobMatchScores,
                ])->render(),
                'next_page_url' => $jobs->nextPageUrl(),
                'has_more' => $jobs->hasMorePages(),
                'from' => $jobs->firstItem(),
                'to' => $jobs->lastItem(),
                'total' => $jobs->total(),
                'personalized' => $jobsPersonalized,
                'ai_ranked' => $jobMatchAiRanked,
            ]);
        }

        $countryLabels = config('hirevo.job_openings_country_labels', []);

        return view('hirevo.job-openings', compact(
            'jobs', 'appliedIds', 'searchQuery', 'searchLocation',
            'filterJobType', 'filterWorkType', 'locationOptions',
            'jobMatchScores', 'jobMatchAiRanked', 'jobsPersonalized', 'personalizeResumeId',
            'countryFilter', 'countryLabels'
        ));
    }

    public function showEmployerJobApply(EmployerJob $job): View|RedirectResponse
    {
        if ($job->status !== 'active') {
            return redirect()->route('job-openings')->with('info', 'This job is no longer accepting applications.');
        }
        if (auth()->check() && ! auth()->user()->isCandidate()) {
            return redirect()->route('job-openings')->with('info', 'Only candidates can apply.');
        }
        if (auth()->check()) {
            if (auth()->user()->isCandidate()) {
                $onboarding = CandidateOnboarding::redirectIfIncomplete(auth()->user());
                if ($onboarding !== null) {
                    return $onboarding;
                }
            }
            $exists = EmployerJobApplication::where('employer_job_id', $job->id)->where('user_id', auth()->id())->exists();
            if ($exists) {
                return redirect()->route('job-openings')->with('info', 'You have already applied for this job.');
            }
        } else {
            return redirect()->route('login', ['redirect' => route('job-openings.apply', $job)]);
        }
        $job->load('user.referrerProfile');
        $user = auth()->user();
        $resumes = $user->resumes()->orderByDesc('created_at')->get();
        $profile = $user->candidateProfile;
        $educationDegrees = CandidateProfile::educationDegreeValues();
        $salaryCurrencies = ['INR', 'USD', 'EUR', 'GBP', 'AED', 'SGD', 'CAD', 'AUD'];
        $noticePeriods = EmployerJobApplication::noticePeriodOptions();

        return view('hirevo.job-openings-apply', compact(
            'job', 'resumes', 'profile', 'educationDegrees', 'salaryCurrencies', 'noticePeriods'
        ));
    }

    public function storeEmployerJobApply(Request $request, EmployerJob $job): RedirectResponse
    {
        if ($job->status !== 'active') {
            return redirect()->route('job-openings')->with('info', 'This job is no longer accepting applications.');
        }
        if (! auth()->user()->isCandidate()) {
            return redirect()->route('job-openings');
        }
        $onboarding = CandidateOnboarding::redirectIfIncomplete(auth()->user());
        if ($onboarding !== null) {
            return $onboarding;
        }
        $exists = EmployerJobApplication::where('employer_job_id', $job->id)->where('user_id', auth()->id())->exists();
        if ($exists) {
            return redirect()->route('job-openings')->with('info', 'You have already applied for this job.');
        }
        $noticeKeys = array_keys(EmployerJobApplication::noticePeriodOptions());
        $validated = $request->validate([
            'full_name'                 => ['required', 'string', 'max:255'],
            'resume_id'                 => ['required', 'integer', 'exists:resumes,id'],
            'cover_message'             => ['nullable', 'string', 'max:2000'],
            'phone'                     => ['required', 'string', 'max:20'],
            'headline'                  => ['required', 'string', 'max:255'],
            'current_company'           => ['nullable', 'string', 'max:255'],
            'education'                 => ['required', 'string', Rule::in(CandidateProfile::educationDegreeValues())],
            'experience_years'          => ['required', 'integer', 'min:0', 'max:50'],
            'experience_months'         => ['required', 'integer', 'min:0', 'max:11'],
            'current_salary'            => ['nullable', 'string', 'max:120'],
            'skills'                    => ['required', 'string', 'max:2000'],
            'location'                  => ['required', 'string', 'max:255'],
            'preferred_job_location'    => ['required', 'string', 'max:255'],
            'expected_salary'           => ['required', 'string', 'max:100'],
            'expected_salary_currency'  => ['required', 'string', 'max:8'],
            'expected_salary_period'    => ['required', Rule::in(['per_annum', 'per_month'])],
            'linkedin_url'              => ['nullable', 'string', 'max:500'],
            'notice_period'             => ['required', 'string', Rule::in($noticeKeys)],
            'info_accurate'             => ['accepted'],
        ], [
            'resume_id.required' => 'Please attach a resume before submitting.',
            'phone.required' => 'Phone number is required.',
            'headline.required' => 'Current role / headline is required.',
            'education.required' => 'Education is required.',
            'experience_years.required' => 'Experience is required.',
            'experience_months.required' => 'Experience (months) is required.',
            'skills.required' => 'Skills are required.',
            'location.required' => 'Current location is required.',
            'preferred_job_location.required' => 'Preferred job location is required.',
            'expected_salary.required' => 'Expected salary is required.',
            'info_accurate.accepted' => 'Please confirm that your information is accurate.',
        ]);
        $user = auth()->user();
        $resume = Resume::where('user_id', $user->id)->find($validated['resume_id']);
        if (! $resume) {
            return back()->withErrors(['resume_id' => 'Selected resume does not belong to your account.'])->withInput();
        }

        $linkedinUrl = $validated['linkedin_url'] ?? null;
        if (is_string($linkedinUrl) && ($linkedinUrl = trim($linkedinUrl)) !== '') {
            if (! preg_match('#^https?://#i', $linkedinUrl)) {
                $linkedinUrl = 'https://'.$linkedinUrl;
            }
            if (! filter_var($linkedinUrl, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['linkedin_url' => 'Please enter a valid LinkedIn URL.'])->withInput();
            }
        } else {
            $linkedinUrl = null;
        }

        $user->update([
            'name'  => $validated['full_name'],
            'phone' => $validated['phone'],
        ]);
        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
        $profile->headline = $validated['headline'];
        $profile->current_company = $validated['current_company'] ?: null;
        $profile->education = $validated['education'];
        $profile->experience_years = (int) $validated['experience_years'];
        $profile->experience_months = (int) $validated['experience_months'];
        $profile->skills = $validated['skills'];
        $profile->location = $validated['location'];
        $profile->preferred_job_location = $validated['preferred_job_location'];
        $profile->linkedin_url = $linkedinUrl;
        $profile->current_salary = $validated['current_salary'] ?: null;
        $profile->expected_salary = $validated['expected_salary'];
        $profile->expected_salary_currency = $validated['expected_salary_currency'];
        $profile->expected_salary_period = $validated['expected_salary_period'];
        $profile->save();

        $application = EmployerJobApplication::create([
            'employer_job_id'            => $job->id,
            'user_id'                    => $user->id,
            'resume_id'                  => (int) $validated['resume_id'],
            'cover_message'              => $request->cover_message ? trim($request->cover_message) : null,
            'notice_period'              => $validated['notice_period'],
            'info_accurate_confirmed_at' => now(),
            'status'                     => 'applied',
        ]);

        // Store ATS score and job match score for employer view
        $resume = $application->resume;
        $atsScore = $resume ? $resume->ai_score : null;
        $resumeText = null;
        if ($resume && Storage::disk('local')->exists($resume->file_path)) {
            $resumeText = app(ResumeAnalysisService::class)->extractTextFromFile(
                Storage::disk('local')->path($resume->file_path),
                $resume->mime_type ?? 'application/pdf'
            );
        }
        if ($resumeText === null || $resumeText === '') {
            // Build minimal candidate summary from profile for match scoring
            $parts = [];
            if ($profile->headline) {
                $parts[] = $profile->headline;
            }
            if ($profile->education) {
                $parts[] = 'Education: ' . $profile->education;
            }
            if ($profile->experience_years !== null || $profile->experience_months) {
                $parts[] = 'Experience: ' . ($profile->formattedTotalExperience() ?? '');
            }
            if ($profile->skills) {
                $parts[] = 'Skills: ' . (is_array($profile->skills) ? implode(', ', $profile->skills) : $profile->skills);
            }
            $resumeText = implode("\n", $parts) ?: 'Candidate profile.';
        }

        // Job match score: try AI first, then always fall back to rule-based so score is set at apply time
        $jobMatchScore = null;
        $jobMatchExplanation = null;
        $resumeAnalysis = app(ResumeAnalysisService::class);
        $requiredSkills = is_array($job->required_skills)
            ? $job->required_skills
            : (is_string($job->required_skills) ? preg_split('/[\r\n,;|]+/', $job->required_skills) : []);
        $requiredSkills = array_values(array_filter(array_map('trim', $requiredSkills ?? []), fn ($s) => $s !== ''));
        if ($resumeText !== '') {
            $gpt = app(GptService::class);
            if ($gpt->isAvailable()) {
                $match = $gpt->getResumeJobMatchScore(
                    $resumeText,
                    $job->title,
                    $job->description ?? '',
                    $requiredSkills
                );
                if ($match !== null) {
                    $jobMatchScore = $match['score'];
                    $jobMatchExplanation = $match['explanation'] ?? null;
                }
            }
            if ($jobMatchScore === null) {
                $match = $resumeAnalysis->getEmployerJobMatchRuleBased(
                    $resumeText,
                    $job->title,
                    $job->description ?? '',
                    $requiredSkills
                );
                $jobMatchScore = $match['score'];
                $jobMatchExplanation = $match['explanation'] ?? null;
            }
        }

        $application->update([
            'ats_score' => $atsScore,
            'job_match_score' => $jobMatchScore,
            'job_match_explanation' => $jobMatchExplanation,
        ]);

        $applyLink = is_string($job->apply_link ?? null) ? trim((string) $job->apply_link) : null;
        if ($applyLink !== '') {
            return redirect()
                ->route('job-openings.apply.external-redirect', $job)
                ->with('success', 'Application saved. The employer’s apply page will open in a new tab.')
                ->with('apply_link', $applyLink);
        }

        return redirect()->route('job-openings')->with('success', 'Your application has been submitted.');
    }

    public function externalEmployerJobApplyRedirect(EmployerJob $job): View|RedirectResponse
    {
        if ($job->status !== 'active') {
            return redirect()->route('job-openings')->with('info', 'This job is no longer accepting applications.');
        }

        $applyLink = session('apply_link');
        if (! is_string($applyLink) || trim($applyLink) === '') {
            return redirect()->route('job-openings')->with('info', 'No external apply link found for this job.');
        }

        $applyLink = trim($applyLink);
        if (! filter_var($applyLink, FILTER_VALIDATE_URL)) {
            return redirect()->route('job-openings')->with('info', 'External apply link is invalid.');
        }

        return view('hirevo.job-apply-redirect', [
            'job' => $job,
            'applyLink' => $applyLink,
        ]);
    }
}
