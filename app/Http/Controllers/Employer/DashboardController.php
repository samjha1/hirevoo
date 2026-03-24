<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\EmployerJobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }

        $profile = $user->referrerProfile;
        $isApproved = $profile && $profile->is_approved;

        $jobs = collect();
        $counts = ['all' => 0, 'active' => 0, 'draft' => 0, 'closed' => 0];
        $applicationCounts = [
            'total' => 0,
            'applied' => 0,
            'shortlisted' => 0,
            'interviewed' => 0,
            'offered' => 0,
            'hired' => 0,
            'rejected' => 0,
        ];
        $report = [
            'avg_match_score' => null,
            'avg_ats_score' => null,
            'shortlist_rate' => 0,
            'hire_rate' => 0,
        ];
        $topJobs = collect();

        if ($isApproved) {
            $jobs = $user->employerJobs()->withCount('applications')->orderByDesc('created_at')->get();
            $counts = [
                'all'    => $user->employerJobs()->count(),
                'active' => $user->employerJobs()->where('status', 'active')->count(),
                'draft'  => $user->employerJobs()->where('status', 'draft')->count(),
                'closed' => $user->employerJobs()->where('status', 'closed')->count(),
            ];

            $applicationBase = EmployerJobApplication::query()
                ->whereHas('employerJob', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });

            $applicationCounts = [
                'total' => (clone $applicationBase)->count(),
                'applied' => (clone $applicationBase)->where('status', EmployerJobApplication::STATUS_APPLIED)->count(),
                'shortlisted' => (clone $applicationBase)->where('status', EmployerJobApplication::STATUS_SHORTLISTED)->count(),
                'interviewed' => (clone $applicationBase)->where('status', EmployerJobApplication::STATUS_INTERVIEWED)->count(),
                'offered' => (clone $applicationBase)->where('status', EmployerJobApplication::STATUS_OFFERED)->count(),
                'hired' => (clone $applicationBase)->where('status', EmployerJobApplication::STATUS_HIRED)->count(),
                'rejected' => (clone $applicationBase)->where('status', EmployerJobApplication::STATUS_REJECTED)->count(),
            ];

            $avgMatch = (clone $applicationBase)->whereNotNull('job_match_score')->avg('job_match_score');
            $avgAts = (clone $applicationBase)->whereNotNull('ats_score')->avg('ats_score');
            $totalApplications = (int) $applicationCounts['total'];
            $shortlistRate = $totalApplications > 0
                ? (int) round(($applicationCounts['shortlisted'] / $totalApplications) * 100)
                : 0;
            $hireRate = $totalApplications > 0
                ? (int) round(($applicationCounts['hired'] / $totalApplications) * 100)
                : 0;

            $report = [
                'avg_match_score' => $avgMatch !== null ? (int) round($avgMatch) : null,
                'avg_ats_score' => $avgAts !== null ? (int) round($avgAts) : null,
                'shortlist_rate' => $shortlistRate,
                'hire_rate' => $hireRate,
            ];

            $topJobs = $user->employerJobs()
                ->withCount('applications')
                ->orderByDesc('applications_count')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        return view('hirevo.employer.dashboard', [
            'profile'    => $profile,
            'isApproved' => $isApproved,
            'jobs'       => $jobs,
            'counts'     => $counts,
            'applicationCounts' => $applicationCounts,
            'report' => $report,
            'topJobs' => $topJobs,
        ]);
    }
}
