<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CandidateDashboardController extends Controller
{
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
            ->get();

        $jobGoalApplications = $user->jobApplications()
            ->with('jobRole')
            ->orderByDesc('created_at')
            ->get();

        return view('hirevo.candidate.dashboard', [
            'employerApplications' => $employerApplications,
            'jobGoalApplications' => $jobGoalApplications,
        ]);
    }
}
