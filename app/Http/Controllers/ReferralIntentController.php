<?php

namespace App\Http\Controllers;

use App\Models\EmployerJob;
use App\Models\JobRole;
use App\Models\Lead;
use App\Models\Resume;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralIntentController extends Controller
{
    /**
     * Record referral interest in `leads`, then redirect to pricing.
     * GET /referral-intent?source=...&job_role_id=...&employer_job_id=...
     */
    public function toPricing(Request $request, ResumeController $resumeController): RedirectResponse
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
                    $resumeController->upsertSkillGapLeadForJobRole($resumeModel, $jobRole);
                    $this->tagLatestLead($user->id, ['job_role_id' => $jobRole->id], $source);
                } elseif ($request->filled('employer_job_id')) {
                    $job = EmployerJob::where('status', 'active')->findOrFail($request->employer_job_id);
                    $resumeController->upsertReferralLeadForEmployerJob($resumeModel, $job);
                    $this->tagLatestLead($user->id, ['employer_job_id' => $job->id], $source);
                } else {
                    $this->storeGenericReferralLead($user->id, $source);
                }
            } else {
                $this->storeReferralLeadWithoutResume(
                    $user->id,
                    $source,
                    $request->filled('job_role_id') ? (int) $request->job_role_id : null,
                    $request->filled('employer_job_id') ? (int) $request->employer_job_id : null
                );
            }
        } else {
            $this->storeGuestOrNonCandidateReferral(
                $source,
                $request->filled('job_role_id') ? (int) $request->job_role_id : null,
                $request->filled('employer_job_id') ? (int) $request->employer_job_id : null
            );
        }

        return redirect()->route('pricing')->with('info', 'Explore plans to unlock referrals and career tools.');
    }

    protected function tagLatestLead(int $candidateId, array $where, string $source): void
    {
        $q = Lead::query()->where('candidate_id', $candidateId);
        foreach ($where as $column => $value) {
            $q->where($column, $value);
        }
        $lead = $q->latest('id')->first();
        $lead?->update(['referral_source' => $source]);
    }

    protected function storeGenericReferralLead(int $candidateId, string $source): void
    {
        Lead::query()->create([
            'candidate_id' => $candidateId,
            'skill_analysis_id' => null,
            'job_role_id' => null,
            'employer_job_id' => null,
            'match_percentage' => null,
            'missing_skills' => null,
            'status' => 'available',
            'referral_source' => $source,
            'lead_summary' => 'referral_intent_generic',
        ]);
    }

    protected function storeReferralLeadWithoutResume(int $candidateId, string $source, ?int $jobRoleId, ?int $employerJobId): void
    {
        Lead::query()->create([
            'candidate_id' => $candidateId,
            'skill_analysis_id' => null,
            'job_role_id' => $jobRoleId,
            'employer_job_id' => $employerJobId,
            'match_percentage' => null,
            'missing_skills' => null,
            'status' => 'available',
            'referral_source' => $source,
            'lead_summary' => 'referral_intent_no_resume',
        ]);
    }

    protected function storeGuestOrNonCandidateReferral(string $source, ?int $jobRoleId, ?int $employerJobId): void
    {
        $meta = ['source' => $source];
        if (auth()->check()) {
            $meta['user_id'] = auth()->id();
            $u = auth()->user();
            $meta['account'] = $u->isCandidate() ? 'candidate' : ($u->isReferrer() ? 'referrer' : 'other');
        }

        Lead::query()->create([
            'candidate_id' => null,
            'skill_analysis_id' => null,
            'job_role_id' => $jobRoleId,
            'employer_job_id' => $employerJobId,
            'match_percentage' => null,
            'missing_skills' => null,
            'status' => 'available',
            'referral_source' => $source,
            'lead_summary' => json_encode($meta),
        ]);
    }
}
