<?php

namespace App\Http\Controllers;

use App\Support\CandidateOnboarding;
use App\Models\JobApplication;
use App\Models\JobRole;
use App\Models\Resume;
use App\Services\GptService;
use App\Services\JobCatalogService;
use App\Support\StoredFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function showApplyForm(Request $request, JobRole $jobRole, ResumeAnalysisService $resumeAnalysis): View|RedirectResponse
    {
        $returnTo = $this->resolveReturnTo($request);

        if (! auth()->check()) {
            return redirect()->route('login', [
                'redirect' => route('job-goal.apply', array_filter([
                    'jobRole' => $jobRole,
                    'return_to' => $returnTo === 'job-openings' ? 'job-openings' : null,
                ])),
            ]);
        }
        if (! auth()->user()->isCandidate()) {
            return redirect()->to($this->returnUrl($jobRole, $returnTo))
                ->with('info', 'Only candidates can apply.');
        }

        $onboarding = CandidateOnboarding::redirectIfIncomplete(auth()->user());
        if ($onboarding !== null) {
            return $onboarding;
        }

        $existing = JobApplication::where('user_id', auth()->id())->where('job_role_id', $jobRole->id)->first();
        if ($existing) {
            return redirect()->to($this->returnUrl($jobRole, $returnTo))
                ->with('info', 'You have already applied for this role.');
        }

        $resumes = auth()->user()->resumes()->orderByDesc('created_at')->get();
        $primaryResume = auth()->user()->resumes()->where('is_primary', true)->first()
            ?? $resumes->first();
        $matchResult = null;
        if ($primaryResume) {
            $matchResult = $resumeAnalysis->getResumeJobRoleMatch($primaryResume, $jobRole);
        }

        return view('hirevo.job-apply', [
            'jobRole' => $jobRole,
            'resumes' => $resumes,
            'matchResult' => $matchResult,
            'primaryResumeId' => $primaryResume?->id,
            'returnTo' => $returnTo,
        ]);
    }

    /**
     * API: get match score for a resume vs job role (for apply form dropdown).
     */
    public function matchScore(Request $request, JobRole $jobRole, ResumeAnalysisService $resumeAnalysis): JsonResponse
    {
        $request->validate(['resume_id' => ['required', 'integer', 'exists:resumes,id']]);
        $resume = Resume::where('user_id', auth()->id())->find($request->resume_id);
        if (! $resume) {
            return response()->json(['error' => 'Invalid resume'], 403);
        }
        $result = $resumeAnalysis->getResumeJobRoleMatch($resume, $jobRole);
        return response()->json([
            'score' => $result['score'],
            'explanation' => $result['explanation'],
        ]);
    }

    public function store(Request $request, JobRole $jobRole, GptService $gptService, ResumeAnalysisService $resumeAnalysis): RedirectResponse
    {
        $request->validate([
            'resume_id' => ['nullable', 'integer', 'exists:resumes,id'],
            'cover_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $returnTo = $this->resolveReturnTo($request);

        if (! auth()->user()->isCandidate()) {
            return redirect()->to($this->returnUrl($jobRole, $returnTo));
        }

        $onboarding = CandidateOnboarding::redirectIfIncomplete(auth()->user());
        if ($onboarding !== null) {
            return $onboarding;
        }

        $existing = JobApplication::where('user_id', auth()->id())->where('job_role_id', $jobRole->id)->first();
        if ($existing) {
            return redirect()->to($this->returnUrl($jobRole, $returnTo))
                ->with('info', 'You have already applied for this role.');
        }

        $resume = null;
        if ($request->resume_id) {
            $resume = Resume::where('user_id', auth()->id())->find($request->resume_id);
            if (! $resume) {
                return back()->withErrors(['resume_id' => 'Invalid resume.']);
            }
        }

        $matchScore = null;
        $matchScoreExplanation = null;
        if ($resume) {
            $jobRole->loadMissing('requiredSkills');
            $requiredSkills = $jobRole->requiredSkills->pluck('skill_name')->all();
            $readPath = StoredFile::localPathForReading($resume->file_path);
            $resumeText = $readPath !== null
                ? $resumeAnalysis->extractTextFromFile($readPath, $resume->mime_type ?? 'application/pdf')
                : '';
            if ($gptService->isAvailable() && $resumeText !== '') {
                $aiResult = $gptService->getResumeJobMatchScore(
                    $resumeText,
                    $jobRole->title,
                    $jobRole->description ?? '',
                    $requiredSkills
                );
                if ($aiResult !== null) {
                    $matchScore = $aiResult['score'];
                    $matchScoreExplanation = $aiResult['explanation'];
                }
            }
            if ($matchScore === null) {
                $ruleResult = $resumeAnalysis->getResumeJobRoleMatch($resume, $jobRole);
                $matchScore = $ruleResult['score'];
                $matchScoreExplanation = $ruleResult['explanation'];
            }
        }

        JobApplication::create([
            'user_id' => auth()->id(),
            'job_role_id' => $jobRole->id,
            'resume_id' => $request->resume_id ?: null,
            'cover_message' => $request->cover_message ? trim($request->cover_message) : null,
            'status' => 'applied',
            'match_score' => $matchScore,
            'match_score_explanation' => $matchScoreExplanation,
        ]);

        if ($returnTo === 'job-openings') {
            app(JobCatalogService::class)->clearOpeningsCatalogCache();
            $request->session()->forget('job_openings_personalized');
        }

        return redirect()->to($this->returnUrl($jobRole, $returnTo))
            ->with('success', 'Your application has been submitted. ' . ($matchScore !== null ? 'Your match score has been saved and will be visible to employers.' : ''));
    }

    protected function resolveReturnTo(Request $request): ?string
    {
        $value = $request->input('return_to', $request->query('return_to'));

        return $value === 'job-openings' ? 'job-openings' : null;
    }

    protected function returnUrl(JobRole $jobRole, ?string $returnTo): string
    {
        if ($returnTo === 'job-openings') {
            return route('job-openings');
        }

        return route('job-goal.show', $jobRole);
    }
}
