<?php

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use App\Models\CareerConsultationRequest;
use App\Models\EmployerJob;
use App\Models\EmployerJobApplication;
use App\Models\JobRole;
use App\Services\CandidateCareerInsightsService;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class CandidateDashboardController extends Controller
{
    private const PER_PAGE = 8;

    /**
     * Candidate dashboard: list all applications (employer jobs + job goals) with status and company.
     */
    public function index(ResumeAnalysisService $resumeAnalysis, CandidateCareerInsightsService $insights): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isCandidate()) {
            return redirect()->route('home')->with('info', 'This page is for candidates.');
        }

        $employerAll = $user->employerJobApplications()
            ->with(['employerJob.user.referrerProfile'])
            ->get();
        $goalAll = $user->jobApplications()
            ->with('jobRole')
            ->get();

        $merged = collect();
        foreach ($employerAll as $application) {
            $merged->push((object) [
                'kind' => 'employer',
                'application' => $application,
                'sort_at' => $application->created_at,
            ]);
        }
        foreach ($goalAll as $application) {
            $merged->push((object) [
                'kind' => 'goal',
                'application' => $application,
                'sort_at' => $application->created_at,
            ]);
        }

        $sorted = $merged->sortByDesc(fn ($row) => $row->sort_at?->timestamp ?? 0)->values();
        $totalApps = $sorted->count();

        $statusFilter = request()->query('status', 'all');
        $validStatuses = ['all', 'applied', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'];
        if (! in_array($statusFilter, $validStatuses, true)) {
            $statusFilter = 'all';
        }

        $applicationStatusCounts = ['all' => $totalApps];
        foreach (['applied', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'] as $statusKey) {
            $applicationStatusCounts[$statusKey] = $sorted
                ->filter(fn ($row) => ($row->application->status ?? 'applied') === $statusKey)
                ->count();
        }

        $filtered = $statusFilter === 'all'
            ? $sorted
            : $sorted->filter(fn ($row) => ($row->application->status ?? 'applied') === $statusFilter)->values();

        $totalFiltered = $filtered->count();
        $lastPage = max(1, (int) ceil($totalFiltered / self::PER_PAGE));
        $page = min($lastPage, max(1, (int) request()->query('apps_page', 1)));
        $slice = $filtered->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        $allApplications = new LengthAwarePaginator(
            $slice,
            $totalFiltered,
            self::PER_PAGE,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'apps_page',
            ]
        );
        $allApplications->appends(['status' => $statusFilter])->fragment('applications');
        $allApplications->withQueryString();

        $activeApps = $user->employerJobApplications()->whereIn('status', ['shortlisted', 'interviewed', 'offered'])->count();
        $hiredCount = $user->employerJobApplications()->where('status', 'hired')->count();
        $avgMatch = $user->employerJobApplications()->whereNotNull('job_match_score')->avg('job_match_score');

        $snapshot = $insights->snapshot($user);
        $primaryResume = $snapshot['resume'];
        $skillFocusRole = $snapshot['target_role'];
        if ($skillFocusRole) {
            $skillFocusRole->loadMissing('requiredSkills');
        }
        $skillGapData = $snapshot['skill_gaps'];
        $dashboardSkillMatched = $skillGapData['matched'] ?? [];
        $dashboardSkillGaps = $skillGapData['gaps'] ?? [];
        $dashboardSkillMatchPct = $skillGapData['match_pct'] ?? null;
        $dashboardSkillMatchLayer = $dashboardSkillMatchPct !== null ? 'cached' : null;
        $skillFocusSource = $this->resolveSkillFocusSource($user, $skillFocusRole);
        $consultGapPayload = CareerConsultationRequest::buildConsultGapPayload(
            $skillFocusRole,
            $dashboardSkillGaps,
            $dashboardSkillMatched
        );

        $candidateProfile = $user->candidateProfile;
        $dashboardRecommendMasters = false;
        $dashboardMastersField = 'your field';

        if ($candidateProfile) {
            $educationSelect = trim((string) ($candidateProfile->education ?? ''));
            $hist = CandidateProfile::jsonRepeaterToArray($candidateProfile->education_history ?? null);
            $eduBlob = $educationSelect;
            foreach ($hist as $row) {
                if (! is_array($row)) {
                    continue;
                }
                foreach (['degree', 'field', 'institution'] as $k) {
                    if (! empty($row[$k])) {
                        $eduBlob .= ' '.(string) $row[$k];
                    }
                }
            }

            $hasPostgrad = (bool) preg_match(
                '/\b(m\.?\s*e\.?|m\.?\s*tech|m\.?\s*sc|m\.?\s*com|mba|m\.?\s*ba|ms\b|masters?\b|post\s*graduate|pgdm|ph\.?\s*d\.?|doctorate)\b/i',
                $eduBlob
            );

            $hasBeOrBtech = in_array($educationSelect, ['B.E.', 'B.Tech'], true)
                || (bool) preg_match(
                    '/\b(b\.?\s*e\.?|b\.?\s*tech|bachelor\s+of\s+engineering)\b/i',
                    $eduBlob
                );

            $dashboardRecommendMasters = $hasBeOrBtech && ! $hasPostgrad;

            foreach ($hist as $row) {
                if (is_array($row) && filled($row['field'] ?? null)) {
                    $dashboardMastersField = trim((string) $row['field']);
                    break;
                }
            }
            if ($dashboardMastersField === 'your field' && $educationSelect !== '') {
                $stripped = preg_replace('/^\s*(b\.?\s*e\.?|b\.?\s*tech)\s*[,:]?\s*/i', '', $educationSelect);
                $stripped = trim((string) $stripped);
                if ($stripped !== '' && mb_strlen($stripped) > 2) {
                    $dashboardMastersField = $stripped;
                }
            }
            if ($dashboardMastersField === 'your field' && $skillFocusRole) {
                $dashboardMastersField = $skillFocusRole->title;
            }
        }

        $profileCompletion = CandidateProfile::completionStats($candidateProfile, $user);

        $employerApps = $user->employerJobApplications()->get();
        $goalApps = $user->jobApplications()->get();

        $trackerCounts = [
            'applied' => $employerApps->where('status', EmployerJobApplication::STATUS_APPLIED)->count()
                + $goalApps->where('status', 'applied')->count(),
            'shortlisted' => $employerApps->where('status', EmployerJobApplication::STATUS_SHORTLISTED)->count()
                + $goalApps->where('status', 'shortlisted')->count(),
            'interview' => $employerApps->where('status', EmployerJobApplication::STATUS_INTERVIEWED)->count()
                + $goalApps->where('status', 'interviewed')->count(),
            'offer' => $employerApps->where('status', EmployerJobApplication::STATUS_OFFERED)->count()
                + $goalApps->where('status', 'offered')->count(),
        ];

        $hiringScore = $this->computeHiringScore(
            $dashboardSkillMatchPct,
            $avgMatch,
            $profileCompletion['percent'] ?? 0,
            $primaryResume !== null,
            $totalApps,
            $hiredCount
        );

        $scoreBreakdown = $this->buildScoreBreakdown(
            $hiringScore,
            $dashboardSkillMatchPct,
            $avgMatch,
            $profileCompletion['percent'] ?? 0,
            $primaryResume !== null,
            $trackerCounts['interview'],
            $trackerCounts['shortlisted']
        );

        $scoreTrend = $this->buildScoreTrend($hiringScore, $sorted);

        $skillGapChart = $insights->skillGapChartFromAnalysis($skillGapData);

        $roadmapSteps = $this->buildRoadmapSteps(
            $primaryResume !== null,
            $goalApps->isNotEmpty(),
            count($dashboardSkillGaps) > 0,
            $profileCompletion['percent'] ?? 0,
            $totalApps,
            $hiredCount > 0
        );

        $strongMinPct = (int) config('hirevo_candidate_features.job_match_min_pct', 45);
        $jobMatches = array_values(array_filter(
            $snapshot['job_matches'],
            static fn (array $job): bool => (int) ($job['match'] ?? 0) >= $strongMinPct
        ));
        $jobMatches = array_slice($jobMatches, 0, 4);

        $percentile = min(95, max(35, (int) round($hiringScore * 0.85 + ($profileCompletion['percent'] ?? 0) * 0.1)));

        $nextStep = $this->buildNextStep(
            $primaryResume,
            $dashboardSkillGaps,
            $skillFocusRole,
            $profileCompletion['percent'] ?? 0,
            $totalApps
        );

        $insights = $this->buildInsights(
            $dashboardSkillMatched,
            $dashboardSkillGaps,
            $trackerCounts['interview'],
            $employerApps->whereNotNull('job_match_score')->avg('job_match_score'),
            $profileCompletion['percent'] ?? 0
        );

        $hiringScoreDetails = $this->buildHiringScoreDetails(
            $primaryResume,
            $dashboardSkillMatchPct,
            $avgMatch,
            $profileCompletion['percent'] ?? 0,
            $totalApps,
            $hiredCount,
            $skillFocusRole,
            $dashboardSkillMatched,
            $dashboardSkillGaps,
            $skillFocusSource,
            $dashboardSkillMatchLayer
        );

        return view('hirevo.candidate.dashboard', [
            'allApplications' => $allApplications,
            'dashboardStats' => [
                'total_apps' => $totalApps,
                'active_reviews' => $activeApps,
                'hired_count' => $hiredCount,
                'avg_match' => $avgMatch,
            ],
            'primaryResume' => $primaryResume,
            'skillFocusRole' => $skillFocusRole,
            'dashboardSkillMatched' => $dashboardSkillMatched,
            'dashboardSkillGaps' => $dashboardSkillGaps,
            'dashboardSkillMatchPct' => $dashboardSkillMatchPct,
            'dashboardSkillMatchLayer' => $dashboardSkillMatchLayer,
            'skillFocusSource' => $skillFocusSource,
            'consultGapPayload' => $consultGapPayload,
            'dashboardRecommendMasters' => $dashboardRecommendMasters,
            'dashboardMastersField' => $dashboardMastersField,
            'profileCompletion' => $profileCompletion,
            'hiringScore' => $hiringScore,
            'hiringScoreLabel' => $this->hiringScoreLabel($hiringScore),
            'scorePercentile' => $percentile,
            'scoreBreakdown' => $scoreBreakdown,
            'scoreTrend' => $scoreTrend,
            'skillGapChart' => $skillGapChart,
            'roadmapSteps' => $roadmapSteps,
            'trackerCounts' => $trackerCounts,
            'jobMatches' => $jobMatches,
            'nextStep' => $nextStep,
            'insights' => $insights,
            'hiringScoreDetails' => $hiringScoreDetails,
            'applicationStatusFilter' => $statusFilter,
            'applicationStatusCounts' => $applicationStatusCounts,
        ]);
    }

    /** @return array<string, mixed> */
    private function buildHiringScoreDetails(
        $primaryResume,
        ?int $skillMatchPct,
        ?float $avgMatch,
        int $profilePct,
        int $totalApps,
        int $hiredCount,
        ?JobRole $skillFocusRole,
        array $matchedSkills,
        array $gapSkills,
        ?string $skillFocusSource,
        ?string $matchLayer
    ): array {
        $skillComponent = $skillMatchPct ?? ($avgMatch !== null ? (int) round($avgMatch) : null);
        if ($skillComponent === null) {
            $skillComponent = $primaryResume ? 55 : 35;
        }

        $activityBonus = min(15, $totalApps * 2);
        $hiredBonus = $hiredCount > 0 ? 10 : 0;
        $resumeAts = $primaryResume?->ai_score;

        $factors = [
            [
                'label' => 'Skills & job match',
                'value' => $skillComponent,
                'display' => $skillComponent.'%',
                'weight' => '45%',
                'detail' => $skillMatchPct !== null
                    ? 'Role/resume skill alignment from your latest analysis.'
                    : ($avgMatch !== null
                        ? 'Based on average match across your job applications.'
                        : 'Estimated until you complete a skill match or apply to jobs.'),
            ],
            [
                'label' => 'Profile completeness',
                'value' => $profilePct,
                'display' => $profilePct.'%',
                'weight' => '35%',
                'detail' => 'Required profile fields and resume on file.',
            ],
            [
                'label' => 'Application activity',
                'value' => $activityBonus + $hiredBonus,
                'display' => '+'.($activityBonus + $hiredBonus).' pts',
                'weight' => 'Bonus',
                'detail' => $totalApps.' application(s)'
                    .($hiredCount > 0 ? ', '.$hiredCount.' hired' : '')
                    .'.',
            ],
        ];

        if ($resumeAts !== null) {
            $factors[] = [
                'label' => 'Resume ATS score',
                'value' => (int) $resumeAts,
                'display' => $resumeAts.'/100',
                'weight' => 'Saved',
                'detail' => 'From your uploaded resume analysis.',
            ];
        }

        $sourceLabels = [
            'applied_goal' => 'Based on your latest job goal application.',
            'resume_top' => 'Based on your best-fit role from your resume.',
        ];

        return [
            'factors' => $factors,
            'resume' => $primaryResume ? [
                'id' => $primaryResume->id,
                'file_name' => $primaryResume->file_name,
                'ai_score' => $primaryResume->ai_score,
                'ai_score_explanation' => $primaryResume->ai_score_explanation,
                'ai_summary' => $primaryResume->ai_summary,
                'skills' => is_array($primaryResume->extracted_skills) ? $primaryResume->extracted_skills : [],
                'analyzed_at' => $primaryResume->updated_at?->gt($primaryResume->created_at)
                    ? $primaryResume->updated_at
                    : $primaryResume->created_at,
            ] : null,
            'role_match' => $skillFocusRole ? [
                'role' => $skillFocusRole,
                'match_pct' => $skillMatchPct,
                'matched_skills' => $matchedSkills,
                'gap_skills' => $gapSkills,
                'source' => $sourceLabels[$skillFocusSource ?? ''] ?? null,
                'match_layer' => $matchLayer,
            ] : null,
            'avg_job_match' => $avgMatch !== null ? (int) round($avgMatch) : null,
        ];
    }

    private function computeHiringScore(
        ?int $skillMatchPct,
        ?float $avgMatch,
        int $profilePct,
        bool $hasResume,
        int $totalApps,
        int $hiredCount
    ): int {
        $skillComponent = $skillMatchPct ?? ($avgMatch !== null ? (int) round($avgMatch) : null);
        if ($skillComponent === null) {
            $skillComponent = $hasResume ? 55 : 35;
        }

        $activityBonus = min(15, $totalApps * 2);
        $hiredBonus = $hiredCount > 0 ? 10 : 0;

        $score = ($skillComponent * 0.45) + ($profilePct * 0.35) + ($activityBonus + $hiredBonus);

        return (int) min(100, max(20, round($score)));
    }

    private function hiringScoreLabel(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Excellent',
            $score >= 65 => 'Good',
            $score >= 50 => 'Fair',
            default => 'Needs work',
        };
    }

    protected function resolveSkillFocusSource($user, ?JobRole $role): ?string
    {
        if (! $role) {
            return null;
        }

        $latestGoalApp = $user->jobApplications()
            ->where('job_role_id', $role->id)
            ->exists();

        return $latestGoalApp ? 'applied_goal' : 'resume_top';
    }

    /** @return list<array{label: string, score: int, icon: string}> */
    private function buildScoreBreakdown(
        int $hiringScore,
        ?int $skillMatchPct,
        ?float $avgMatch,
        int $profilePct,
        bool $hasResume,
        int $interviewCount,
        int $shortlistedCount
    ): array {
        $technical = $skillMatchPct ?? ($avgMatch !== null ? (int) round($avgMatch) : ($hasResume ? 58 : 40));
        $communication = min(100, max(35, (int) round($profilePct * 0.85 + ($shortlistedCount > 0 ? 12 : 0))));
        $interviewReadiness = min(100, max(30, (int) round(40 + ($interviewCount * 15) + ($hiringScore * 0.2))));
        $resumeQuality = $hasResume ? min(100, max(50, (int) round($profilePct * 0.7 + $technical * 0.3))) : 35;
        $problemSolving = min(100, max(35, (int) round($technical * 0.9 + 8)));
        $culturalFit = min(100, max(40, (int) round($hiringScore * 0.75 + ($shortlistedCount * 5))));

        return [
            ['label' => 'Communication', 'score' => $communication, 'icon' => 'mdi-message-text-outline'],
            ['label' => 'Technical Skills', 'score' => $technical, 'icon' => 'mdi-code-tags'],
            ['label' => 'Problem Solving', 'score' => $problemSolving, 'icon' => 'mdi-lightbulb-on-outline'],
            ['label' => 'Interview Readiness', 'score' => $interviewReadiness, 'icon' => 'mdi-account-voice'],
            ['label' => 'Resume Quality', 'score' => $resumeQuality, 'icon' => 'mdi-file-document-outline'],
            ['label' => 'Cultural Fit', 'score' => $culturalFit, 'icon' => 'mdi-handshake-outline'],
        ];
    }

    /** @return list<array{month: string, score: int}> */
    private function buildScoreTrend(int $currentScore, $sortedApplications): array
    {
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->format('M');
        })->all();

        $start = max(25, $currentScore - 18);
        $step = ($currentScore - $start) / 5;

        return collect($months)->map(function ($month, $idx) use ($start, $step, $currentScore) {
            $score = (int) round($start + ($step * $idx));
            if ($idx === 5) {
                $score = $currentScore;
            }

            return ['month' => $month, 'score' => min(100, max(20, $score))];
        })->all();
    }

    /** @return list<array{skill: string, current: int, recommended: int}> */
    private function buildSkillGapChart(array $gaps, array $matched, ?JobRole $role): array
    {
        $items = [];
        foreach (array_slice($gaps, 0, 5) as $skill) {
            $hash = crc32($skill);
            $items[] = [
                'skill' => $skill,
                'current' => 28 + ($hash % 25),
                'recommended' => 78 + ($hash % 15),
            ];
        }

        if ($items !== []) {
            return $items;
        }

        foreach (array_slice($matched, 0, 3) as $skill) {
            $hash = crc32($skill);
            $items[] = [
                'skill' => $skill,
                'current' => 62 + ($hash % 17),
                'recommended' => 82 + ($hash % 14),
            ];
        }

        if ($items === [] && $role) {
            $labels = $role->requiredSkills->pluck('name')->take(5)->all();
            foreach ($labels as $label) {
                $items[] = [
                    'skill' => $label,
                    'current' => 40,
                    'recommended' => 85,
                ];
            }
        }

        if ($items === []) {
            return [
                ['skill' => 'Core Skills', 'current' => 45, 'recommended' => 85],
                ['skill' => 'Tools & Tech', 'current' => 50, 'recommended' => 88],
                ['skill' => 'Soft Skills', 'current' => 55, 'recommended' => 80],
            ];
        }

        return $items;
    }

    /** @return list<array{label: string, status: string}> */
    private function buildRoadmapSteps(
        bool $hasResume,
        bool $hasGoalApp,
        bool $hasGaps,
        int $profilePct,
        int $totalApps,
        bool $isHired
    ): array {
        $assessmentsDone = $hasResume && ($hasGoalApp || $profilePct >= 60);
        $profileBuilt = $profilePct >= 75;
        $applied = $totalApps > 0;

        return [
            [
                'label' => 'Complete Assessments',
                'status' => $assessmentsDone ? 'completed' : ($hasResume ? 'in_progress' : 'pending'),
                'description' => 'Take skill assessments based on your resume and target role.',
                'action_label' => 'Take assessments',
                'action_url' => route('candidate.assessments'),
            ],
            [
                'label' => 'Improve Weak Areas',
                'status' => $hasGaps ? 'in_progress' : ($assessmentsDone ? 'completed' : 'pending'),
                'description' => 'Close skill gaps identified from your resume and target roles.',
                'action_label' => 'View skill gaps',
                'action_url' => route('candidate.skill-gaps'),
            ],
            [
                'label' => 'Build Profile',
                'status' => $profileBuilt ? 'completed' : ($profilePct >= 40 ? 'in_progress' : 'pending'),
                'description' => 'A complete profile improves match accuracy and recruiter visibility.',
                'action_label' => 'Complete profile',
                'action_url' => route('profile'),
            ],
            [
                'label' => 'Apply to Jobs',
                'status' => $applied ? ($isHired ? 'completed' : 'in_progress') : 'pending',
                'description' => 'Apply to matched openings and track every stage in one place.',
                'action_label' => 'Browse jobs',
                'action_url' => route('job-openings'),
            ],
            [
                'label' => 'Land Job',
                'status' => $isHired ? 'completed' : 'pending',
                'description' => 'Move through interviews and offers — your end goal.',
                'action_label' => 'Track applications',
                'action_url' => route('candidate.dashboard').'#applications',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function buildJobMatches($user, $primaryResume, ResumeAnalysisService $resumeAnalysis): array
    {
        $appliedJobIds = $user->employerJobApplications()->pluck('employer_job_id')->all();
        $matches = [];

        if ($primaryResume) {
            $goals = $resumeAnalysis->getMatchingJobGoalsForResume($primaryResume, 3);
            foreach ($goals as $row) {
                $role = $row['job_role'] ?? null;
                if (! $role instanceof JobRole) {
                    continue;
                }
                $matches[] = [
                    'type' => 'goal',
                    'title' => $role->title,
                    'company' => 'Job goal',
                    'location' => 'Skill-based match',
                    'experience' => 'Varies',
                    'salary' => '—',
                    'match' => (int) ($row['match_pct'] ?? 0),
                    'url' => route('job-goal.show', $role),
                ];
            }
        }

        $jobs = EmployerJob::where('status', 'active')
            ->when($appliedJobIds !== [], fn ($q) => $q->whereNotIn('id', $appliedJobIds))
            ->with(['user.referrerProfile'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($jobs as $job) {
            if (count($matches) >= 4) {
                break;
            }
            $company = $job->company_name ?? ($job->user?->referrerProfile?->company_name ?? 'Company');

            $matchScore = null;
            if ($primaryResume && filled($job->required_skills)) {
                $skillsRaw = $job->required_skills;
                $skills = is_array($skillsRaw)
                    ? array_values(array_filter(array_map('strval', $skillsRaw)))
                    : preg_split('/[\s,;|]+/', (string) $skillsRaw, -1, PREG_SPLIT_NO_EMPTY);
                if ($skills !== []) {
                    $cov = $resumeAnalysis->matchResumeToRequiredSkillNames(
                        $primaryResume,
                        array_slice($skills, 0, 12),
                        $user->candidateProfile?->skills
                    );
                    $matchScore = $cov['match_pct'];
                }
            }

            $matches[] = [
                'type' => 'employer',
                'title' => $job->title,
                'company' => $company,
                'location' => $job->formatted_location ?? ($job->location ?? 'Remote'),
                'experience' => $job->experience_years !== null
                    ? $job->experience_years.'+ yrs'
                    : 'Not specified',
                'salary' => $job->formatted_salary_summary ?? '—',
                'match' => $matchScore ?? 75,
                'url' => route('job-openings'),
            ];
        }

        usort($matches, fn ($a, $b) => ($b['match'] ?? 0) <=> ($a['match'] ?? 0));

        return array_slice($matches, 0, 4);
    }

    /** @return array{title: string, description: string, url: string, label: string} */
    private function buildNextStep($primaryResume, array $gaps, ?JobRole $role, int $profilePct, int $totalApps): array
    {
        if (! $primaryResume) {
            return [
                'title' => 'Upload Your Resume',
                'description' => 'Add your resume to unlock hiring score, skill gaps, and personalized job matches.',
                'url' => route('resume.upload'),
                'label' => 'Upload Now',
            ];
        }

        if ($profilePct < 70) {
            return [
                'title' => 'Complete Your Profile',
                'description' => 'Profiles above 80% completeness get significantly more recruiter views.',
                'url' => route('profile'),
                'label' => 'Complete Profile',
            ];
        }

        if (count($gaps) > 0 && $role) {
            return [
                'title' => 'Improve Interview Readiness',
                'description' => 'Close skill gaps for '.$role->title.' to boost your match score and interview chances.',
                'url' => route('job-goal.show', $role),
                'label' => 'Start Now',
            ];
        }

        if ($totalApps === 0) {
            return [
                'title' => 'Apply to Matched Jobs',
                'description' => 'You have strong matches waiting — start applying to move your pipeline forward.',
                'url' => route('candidate.job-matches'),
                'label' => 'View matches',
            ];
        }

        return [
            'title' => 'Keep Momentum Going',
            'description' => 'Track applications, follow your roadmap, and keep improving weak areas.',
            'url' => route('candidate.dashboard').'#applications',
            'label' => 'View Applications',
        ];
    }

    /** @return list<array{label: string, value: string, trend: list<int>, color: string}> */
    private function buildInsights(array $matched, array $gaps, int $interviewCount, ?float $avgMatch, int $profilePct): array
    {
        $bestSkill = $matched[0] ?? ($gaps[0] ?? 'Profile strength');
        $interviewScore = $interviewCount > 0
            ? min(95, 60 + ($interviewCount * 8))
            : ($avgMatch !== null ? (int) round($avgMatch * 0.85) : 0);

        return [
            [
                'label' => 'Best Performing Skill',
                'value' => $bestSkill,
                'trend' => [40, 55, 48, 62, 70, 78],
                'color' => '#10b981',
            ],
            [
                'label' => 'Average Interview Score',
                'value' => $interviewScore > 0 ? $interviewScore.'/100' : '—',
                'trend' => [30, 42, 50, 55, 60, max(30, $interviewScore)],
                'color' => '#6366f1',
            ],
            [
                'label' => 'Profile Views',
                'value' => max(12, (int) round($profilePct * 0.6 + ($interviewCount * 5))),
                'trend' => [20, 28, 35, 42, 50, max(20, (int) round($profilePct * 0.5))],
                'color' => '#f59e0b',
            ],
        ];
    }
}
