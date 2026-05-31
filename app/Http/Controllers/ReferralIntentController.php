<?php

namespace App\Http\Controllers;

use App\Models\EmployerJob;
use App\Models\JobRole;
use App\Models\Resume;
use App\Services\CandidateLeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralIntentController extends Controller
{
    /**
     * Record referral interest in `leads`, then redirect to pricing.
     * GET /referral-intent?source=...&job_role_id=...&employer_job_id=...
     */
    public function toPricing(Request $request, CandidateLeadService $candidateLeads): RedirectResponse
    {
        $request->validate([
            'source' => ['required', 'string', 'max:100'],
            'job_role_id' => ['nullable', 'integer', 'exists:job_roles,id'],
            'employer_job_id' => ['nullable', 'integer', 'exists:employer_jobs,id'],
        ]);

        if ($request->filled('job_role_id') && $request->filled('employer_job_id')) {
            return redirect()->route('pricing');
        }

        $source = $request->query('source', 'unknown');

        if (auth()->check() && auth()->user()->isCandidate()) {
            $user = auth()->user();
            /** @var Resume|null $resumeModel */
            $resumeModel = $user->resumes()->where('is_primary', true)->first()
                ?? $user->resumes()->orderByDesc('created_at')->first();

            if ($resumeModel) {
                if ($request->filled('job_role_id')) {
                    $jobRole = JobRole::with('requiredSkills')->findOrFail($request->job_role_id);
                    $candidateLeads->recordSkillGapLead($resumeModel, $jobRole);
                    $candidateLeads->tagLatestLead($user->id, ['job_role_id' => $jobRole->id], $source);
                } elseif ($request->filled('employer_job_id')) {
                    $job = EmployerJob::where('status', 'active')->findOrFail($request->employer_job_id);
                    $candidateLeads->recordEmployerJobLead($resumeModel, $job, null, $source);
                    $candidateLeads->tagLatestLead($user->id, ['employer_job_id' => $job->id], $source);
                } else {
                    $candidateLeads->recordGenericReferralLead($user->id, $source);
                }
            } else {
                $candidateLeads->recordReferralLeadWithoutResume(
                    $user->id,
                    $source,
                    $request->filled('job_role_id') ? (int) $request->job_role_id : null,
                    $request->filled('employer_job_id') ? (int) $request->employer_job_id : null
                );
            }
        } else {
            $candidateLeads->recordGuestReferral(
                $source,
                $request->filled('job_role_id') ? (int) $request->job_role_id : null,
                $request->filled('employer_job_id') ? (int) $request->employer_job_id : null
            );
        }

        return redirect()->route('pricing')->with('info', 'Explore plans to unlock referrals and career tools.');
    }
}
