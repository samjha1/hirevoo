<?php

namespace App\Http\Controllers;

use App\Models\CareerConsultationRequest;
use App\Models\JobRole;
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
    public function index(ResumeAnalysisService $resumeAnalysis): View|RedirectResponse
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
        $lastPage = max(1, (int) ceil($totalApps / self::PER_PAGE));
        $page = min($lastPage, max(1, (int) request()->query('apps_page', 1)));
        $slice = $sorted->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        $allApplications = new LengthAwarePaginator(
            $slice,
            $totalApps,
            self::PER_PAGE,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'apps_page',
                'fragment' => 'applications',
            ]
        );
        $allApplications->withQueryString();

        $activeApps = $user->employerJobApplications()->whereIn('status', ['shortlisted', 'interviewed', 'offered'])->count();
        $hiredCount = $user->employerJobApplications()->where('status', 'hired')->count();
        $avgMatch = $user->employerJobApplications()->whereNotNull('job_match_score')->avg('job_match_score');

        $primaryResume = $user->resumes()->where('is_primary', true)->first()
            ?? $user->resumes()->orderByDesc('created_at')->first();

        $skillFocusRole = null;
        $dashboardSkillMatched = [];
        $dashboardSkillGaps = [];
        $dashboardSkillMatchPct = null;
        $dashboardSkillMatchLayer = null;
        $skillFocusSource = null;
        $consultGapPayload = [
            'display_gaps' => [],
            'suggested_only' => [],
            'actual_gaps' => [],
        ];

        if ($primaryResume) {
            $latestGoalApp = $user->jobApplications()
                ->with(['jobRole.requiredSkills'])
                ->orderByDesc('created_at')
                ->first();
            if ($latestGoalApp?->jobRole) {
                $latestGoalApp->jobRole->loadMissing('requiredSkills');
                if ($latestGoalApp->jobRole->requiredSkills->isNotEmpty()) {
                    $skillFocusRole = $latestGoalApp->jobRole;
                    $skillFocusSource = 'applied_goal';
                }
            }
            if (! $skillFocusRole) {
                $topGoals = $resumeAnalysis->getMatchingJobGoalsForResume($primaryResume, 1);
                if (! empty($topGoals[0]['job_role']) && $topGoals[0]['job_role'] instanceof JobRole) {
                    $skillFocusRole = $topGoals[0]['job_role'];
                    $skillFocusRole->loadMissing('requiredSkills');
                    if ($skillFocusRole->requiredSkills->isNotEmpty()) {
                        $skillFocusSource = 'resume_top';
                    } else {
                        $skillFocusRole = null;
                    }
                }
            }

            if ($skillFocusRole && $skillFocusRole->requiredSkills->isNotEmpty()) {
                $requiredOrdered = $resumeAnalysis->orderedRequiredSkillLabels($skillFocusRole);
                $coverage = $resumeAnalysis->matchResumeToRequiredSkillNames(
                    $primaryResume,
                    $requiredOrdered,
                    $user->candidateProfile?->skills
                );
                $dashboardSkillMatched = $coverage['matched_display'];
                $dashboardSkillGaps = $coverage['gaps_display'];
                $dashboardSkillMatchPct = $coverage['match_pct'];
                $dashboardSkillMatchLayer = $coverage['match_layer'];
                $consultGapPayload = CareerConsultationRequest::buildConsultGapPayload(
                    $skillFocusRole,
                    $dashboardSkillGaps,
                    $dashboardSkillMatched
                );
            }
        }

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
        ]);
    }
}
