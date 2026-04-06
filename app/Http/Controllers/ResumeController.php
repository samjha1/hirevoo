<?php

namespace App\Http\Controllers;

use App\Models\EmployerJob;
use App\Models\EmployerJobApplication;
use App\Models\JobApplication;
use App\Models\JobRole;
use App\Models\Lead;
use App\Models\Resume;
use App\Models\SkillAnalysis;
use App\Services\CandidateProfileFillerFromResume;
use App\Services\GptService;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResumeController extends Controller
{
    public function __construct(
        protected ResumeAnalysisService $resumeAnalysis,
        protected GptService $gptService,
        protected CandidateProfileFillerFromResume $profileFiller
    ) {}

    public function showUploadForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (! $user->isCandidate()) {
                return redirect()->route('home')->with('info', 'Resume upload is for candidates.');
            }
        }

        return view('hirevo.resume-upload');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'resume.required' => 'Please select a PDF file to upload.',
            'resume.mimes' => 'Only PDF files are supported.',
            'resume.max' => 'The file must not exceed 10 MB.',
        ]);

        $user = auth()->user();
        if (! $user->isCandidate()) {
            return redirect()->route('home');
        }

        set_time_limit((int) config('hirevo.resume_analysis_time_limit', 180));

        $file = $request->file('resume');
        $path = $file->store('resumes', 'local');
        if ($path === false) {
            return back()->withErrors(['resume' => 'Failed to store file.'])->withInput();
        }

        $user->resumes()->update(['is_primary' => false]);

        $resume = $user->resumes()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'is_primary' => true,
        ]);

        $this->resumeAnalysis->analyzeResume($resume);

        $profileFilled = $this->profileFiller->fill($user);

        $user->refresh();
        $user->syncCandidateProfileCompletion();

        if ($request->input('return_to') === 'profile') {
            return redirect()->route('profile')
                ->with(
                    'success',
                    $profileFilled
                        ? 'Resume uploaded and analysed. Your profile was filled from the file — review all sections and save.'
                        : 'Resume uploaded and analysed. We saved basic details from your file; complete any missing sections on your profile.'
                );
        }

        if ($request->input('return_to') === 'job-openings') {
            $request->session()->forget('job_openings_personalized');
            $request->session()->put('job_openings_personalize_resume_id', $resume->id);

            $joCountry = strtolower((string) $request->input('jo_country', ''));
            $validCountries = ['ca', 'us', 'gb', 'ae'];
            $qs = array_filter([
                'q' => $request->input('jo_q'),
                'location' => $request->input('jo_location'),
                'job_type' => $request->input('jo_job_type'),
                'work_location_type' => $request->input('jo_work_type'),
                'country' => in_array($joCountry, $validCountries, true) ? $joCountry : null,
            ], fn ($v) => $v !== null && $v !== '');

            return redirect()->route('job-openings', $qs)
                ->with(
                    'success',
                    $profileFilled
                        ? 'Resume analyzed. Listings below are ordered for your profile — tweak filters any time.'
                        : 'Resume analyzed. Listings below are ranked for your resume — adjust search or filters as needed.'
                );
        }

        return redirect()->route('resume.results', $resume)
            ->with(
                'success',
                $profileFilled
                    ? 'Resume analyzed successfully. Your profile was updated from the file. View your ATS score and jobs below.'
                    : 'Resume analyzed successfully. View your ATS score and recommended jobs below.'
            );
    }

    /**
     * Serve the candidate's own resume PDF (inline view or download). Owner-only.
     */
    public function serveFile(Request $request, Resume $resume): BinaryFileResponse|RedirectResponse
    {
        if ($resume->user_id !== auth()->id()) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($resume->file_path)) {
            return redirect()->route('profile')->with('info', 'That resume file is no longer on the server.');
        }

        $path = Storage::disk('local')->path($resume->file_path);
        $filename = $resume->file_name ?: 'resume.pdf';

        if ($request->boolean('download')) {
            return response()->download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function results(Resume $resume, Request $request): View|RedirectResponse
    {
        if ($resume->user_id !== auth()->id()) {
            abort(403);
        }

        $recommendedJobGoals = $this->getRecommendedJobGoals($resume);
        $recommendedEmployerJobs = $this->getRecommendedEmployerJobs($resume);
        $appliedJobIds = JobApplication::where('user_id', auth()->id())->pluck('job_role_id')->all();
        $appliedEmployerJobIds = EmployerJobApplication::where('user_id', auth()->id())->pluck('employer_job_id')->all();

        $kind = $request->query('kind', 'all');
        if (! in_array($kind, ['all', 'employer', 'goal'], true)) {
            $kind = 'all';
        }

        $mergedAll = $this->buildMergedResumeMatches($recommendedEmployerJobs, $recommendedJobGoals);
        $totals = [
            'all' => count($mergedAll),
            'employer' => count(array_filter($mergedAll, fn ($e) => $e['kind'] === 'employer')),
            'goal' => count(array_filter($mergedAll, fn ($e) => $e['kind'] === 'goal')),
        ];

        $filtered = match ($kind) {
            'employer' => array_values(array_filter($mergedAll, fn ($e) => $e['kind'] === 'employer')),
            'goal' => array_values(array_filter($mergedAll, fn ($e) => $e['kind'] === 'goal')),
            default => $mergedAll,
        };

        $perPage = 9;
        $totalFiltered = count($filtered);
        $lastPage = max(1, (int) ceil($totalFiltered / $perPage));
        $page = min($lastPage, max(1, (int) $request->query('matches_page', 1)));
        $items = array_slice($filtered, ($page - 1) * $perPage, $perPage);

        $matchesPaginator = new LengthAwarePaginator(
            $items,
            $totalFiltered,
            $perPage,
            $page,
            [
                'path' => route('resume.results', $resume),
                'pageName' => 'matches_page',
            ]
        );
        $matchesPaginator->appends(['kind' => $kind]);

        return view('hirevo.resume-results', [
            'resume' => $resume,
            'appliedJobIds' => $appliedJobIds,
            'appliedEmployerJobIds' => $appliedEmployerJobIds,
            'matchesPaginator' => $matchesPaginator,
            'resumeMatchKind' => $kind,
            'resumeMatchTotals' => $totals,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $recommendedEmployerJobs
     * @param  array<int, array<string, mixed>>  $recommendedJobGoals
     * @return list<array{kind: string, match: int, payload: array<string, mixed>}>
     */
    protected function buildMergedResumeMatches(array $recommendedEmployerJobs, array $recommendedJobGoals): array
    {
        $merged = [];
        foreach ($recommendedEmployerJobs as $item) {
            $merged[] = ['kind' => 'employer', 'match' => (int) ($item['match_percentage'] ?? 0), 'payload' => $item];
        }
        foreach ($recommendedJobGoals as $item) {
            $merged[] = ['kind' => 'goal', 'match' => (int) ($item['match_percentage'] ?? 0), 'payload' => $item];
        }
        usort($merged, fn ($a, $b) => $b['match'] <=> $a['match']);

        return array_values($merged);
    }

    /**
     * Create lead (get help to learn) for a job role from results page.
     */
    public function createLead(Request $request): RedirectResponse
    {
        $request->validate([
            'resume_id' => ['required', 'integer'],
            'job_role_id' => ['required', 'integer', 'exists:job_roles,id'],
        ]);

        $resume = Resume::where('user_id', auth()->id())->findOrFail($request->resume_id);
        $jobRole = JobRole::with('requiredSkills')->findOrFail($request->job_role_id);

        $extracted = $resume->getExtractedSkillsList();
        $required = $jobRole->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();
        $matched = array_values(array_intersect($required, $extracted));
        $missing = array_values(array_diff($required, $extracted));
        $matchPercentage = count($required) > 0
            ? (int) round((count($matched) / count($required)) * 100)
            : 0;

        $skillAnalysis = SkillAnalysis::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'job_role_id' => $jobRole->id,
            ],
            [
                'resume_id' => $resume->id,
                'match_percentage' => $matchPercentage,
                // Force JSON for older Laravel versions that don't auto-cast on save
                'matched_skills' => json_encode($matched),
                'missing_skills' => json_encode($missing),
            ]
        );

        Lead::firstOrCreate(
            [
                'candidate_id' => auth()->id(),
                'skill_analysis_id' => $skillAnalysis->id,
            ],
            [
                'job_role_id' => $jobRole->id,
                'match_percentage' => $matchPercentage,
                // Store as JSON string; model will cast back to array on read
                'missing_skills' => json_encode($missing),
                'status' => 'available',
            ]
        );

        return redirect()->route('resume.results', $resume)
            ->with('success', 'You have opted in for learning help. EdTech partners can now bid for your lead.');
    }

    protected function getRecommendedJobGoals(Resume $resume): array
    {
        $extracted = $resume->getExtractedSkillsList();
        $roles = JobRole::where('is_active', true)->with('requiredSkills')->get();
        $scored = [];
        foreach ($roles as $role) {
            $required = $role->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();
            if (count($required) === 0) {
                $scored[] = ['job_role' => $role, 'match_percentage' => 0, 'missing_skills' => []];
                continue;
            }
            $matched = array_values(array_intersect($required, $extracted));
            $missing = array_values(array_diff($required, $extracted));
            $matchPercentage = (int) round((count($matched) / count($required)) * 100);
            $scored[] = [
                'job_role' => $role,
                'match_percentage' => $matchPercentage,
                'missing_skills' => $missing,
            ];
        }
        usort($scored, fn ($a, $b) => $b['match_percentage'] <=> $a['match_percentage']);

        return $scored;
    }

    /**
     * Get recommended posted jobs (EmployerJob) based on resume skills and summary.
     */
    protected function getRecommendedEmployerJobs(Resume $resume): array
    {
        $jobs = EmployerJob::where('status', 'active')
            ->with('user.referrerProfile')
            ->orderByDesc('created_at')
            ->get();

        $skills = $resume->getExtractedSkillsList();
        $summaryWords = [];
        if (! empty($resume->ai_summary)) {
            $summaryWords = array_filter(
                preg_split('/[\s,;.\-\/]+/', strip_tags($resume->ai_summary), -1, PREG_SPLIT_NO_EMPTY),
                fn ($w) => strlen($w) > 2
            );
            $summaryWords = array_map('strtolower', array_unique(array_slice($summaryWords, 0, 50)));
        }

        $scored = [];
        foreach ($jobs as $job) {
            $jobText = strtolower(($job->title ?? '') . ' ' . strip_tags($job->description ?? ''));
            $skillsMatched = 0;
            $totalSkills = count(array_filter($skills, fn ($s) => strlen($s) >= 2));
            foreach ($skills as $skill) {
                if (strlen($skill) >= 2 && str_contains($jobText, $skill)) {
                    $skillsMatched++;
                }
            }
            $matchPercentage = $totalSkills > 0
                ? (int) round(($skillsMatched / $totalSkills) * 100)
                : 0;
            if ($matchPercentage === 0 && ! empty($summaryWords)) {
                $summaryMatches = 0;
                foreach (array_slice($summaryWords, 0, 20) as $word) {
                    if (str_contains($jobText, $word)) {
                        $summaryMatches++;
                    }
                }
                $matchPercentage = (int) round(min(100, (count(array_slice($summaryWords, 0, 20)) > 0 ? ($summaryMatches / 20) * 100 : 0)));
            }
            $scored[] = [
                'job' => $job,
                'match_percentage' => min(100, $matchPercentage),
            ];
        }

        usort($scored, fn ($a, $b) => $b['match_percentage'] <=> $a['match_percentage']);

        return $scored;
    }
}
