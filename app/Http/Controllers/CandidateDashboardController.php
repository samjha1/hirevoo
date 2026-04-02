<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CandidateDashboardController extends Controller
{
    private const PER_PAGE = 8;

    /**
     * Candidate dashboard: list all applications (employer jobs + job goals) with status and company.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isCandidate()) {
            return redirect()->route('home')->with('info', 'This page is for candidates.');
        }

        $employerApplications = $user->employerJobApplications()
            ->with(['employerJob.user.referrerProfile'])
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE, ['*'], 'employer_page')
            ->withQueryString()
            ->fragment('employer-apps');

        $jobGoalApplications = $user->jobApplications()
            ->with('jobRole')
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE, ['*'], 'goal_page')
            ->withQueryString()
            ->fragment('goal-apps');

        $totalApps = $user->employerJobApplications()->count() + $user->jobApplications()->count();
        $activeApps = $user->employerJobApplications()->whereIn('status', ['shortlisted', 'interviewed', 'offered'])->count();
        $hiredCount = $user->employerJobApplications()->where('status', 'hired')->count();
        $avgMatch = $user->employerJobApplications()->whereNotNull('job_match_score')->avg('job_match_score');

        return view('hirevo.candidate.dashboard', [
            'employerApplications' => $employerApplications,
            'jobGoalApplications' => $jobGoalApplications,
            'dashboardStats' => [
                'total_apps' => $totalApps,
                'active_reviews' => $activeApps,
                'hired_count' => $hiredCount,
                'avg_match' => $avgMatch,
            ],
        ]);
    }
}
