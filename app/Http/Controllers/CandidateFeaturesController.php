<?php

namespace App\Http\Controllers;

use App\Services\CandidateCareerInsightsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CandidateFeaturesController extends Controller
{
    public function __construct(
        protected CandidateCareerInsightsService $insights,
    ) {}

    public function assessments(): View|RedirectResponse
    {
        return $this->renderFeature('assessments', 'Skill Assessments', 'Profile-based quizzes to benchmark your readiness.');
    }

    public function mockInterviews(): View|RedirectResponse
    {
        return $this->renderFeature('mock-interviews', 'Mock Interviews', 'Practice behavioral, technical, and HR questions for your target role.');
    }

    public function skillGaps(): View|RedirectResponse
    {
        return $this->renderFeature('skill-gaps', 'Skill Gap Analysis', 'Skills to learn based on your resume and target role.');
    }

    public function jobMatches(): View|RedirectResponse
    {
        return $this->renderFeature('job-matches', 'Job Matches', 'Jobs ranked by fit with your resume — not random listings.');
    }

    public function salaryInsights(): View|RedirectResponse
    {
        return $this->renderFeature('salary-insights', 'Salary Insights', 'Market salary bands and how your expectations compare.');
    }

    protected function renderFeature(string $view, string $title, string $subtitle): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isCandidate()) {
            return redirect()->route('home')->with('info', 'This page is for candidates.');
        }

        $snapshot = $this->insights->snapshot($user);

        return view('hirevo.candidate.'.$view, [
            'pageTitle' => $title,
            'pageSubtitle' => $subtitle,
            'snapshot' => $snapshot,
            'resume' => $snapshot['resume'],
            'targetRole' => $snapshot['target_role'],
            'skillGaps' => $snapshot['skill_gaps'],
            'jobMatches' => $snapshot['job_matches'],
            'salary' => $snapshot['salary'],
            'assessments' => $snapshot['assessments'],
            'mockInterviews' => $snapshot['mock_interviews'],
        ]);
    }
}
