<?php

namespace App\Services;

use App\Models\EmployerJob;
use App\Models\JobRole;
use App\Models\Lead;
use App\Models\Resume;
use App\Models\SkillAnalysis;
use App\Models\UpskillOpportunity;

/**
 * Single entry point for writing candidate rows into `leads` (CRM talent pipeline reads this table).
 * One lead row per candidate — repeat activity updates context and increases intent_score.
 */
class CandidateLeadService
{
    public function __construct(
        protected ResumeAnalysisService $resumeAnalysis,
    ) {
    }

    /**
     * First touch: candidate uploaded resume / has activity but no lead row yet.
     * Repeat uploads bump intent_score instead of creating another row.
     */
    public function ensureCandidateCrmLead(int $candidateId, string $source = 'resume_upload'): Lead
    {
        $lead = $this->findCandidateLead($candidateId);

        if ($lead) {
            $lead->update([
                'intent_score' => $this->applyActivityIntent($lead, 3),
                'referral_source' => $source,
            ]);

            return $lead->fresh();
        }

        return Lead::query()->create([
            'candidate_id' => $candidateId,
            'skill_analysis_id' => null,
            'job_role_id' => null,
            'employer_job_id' => null,
            'upskill_opportunity_id' => null,
            'match_percentage' => null,
            'missing_skills' => null,
            'intent_score' => 10,
            'status' => 'available',
            'referral_source' => $source,
            'lead_summary' => 'profile_active',
        ]);
    }

    public function recordSkillGapLead(Resume $resume, JobRole $jobRole): Lead
    {
        $extracted = $resume->getExtractedSkillsList();
        $required = $jobRole->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();
        $matched = array_values(array_intersect($required, $extracted));
        $missing = array_values(array_diff($required, $extracted));
        $matchPercentage = count($required) > 0
            ? (int) round((count($matched) / count($required)) * 100)
            : 0;

        $skillAnalysis = SkillAnalysis::updateOrCreate(
            [
                'user_id' => $resume->user_id,
                'job_role_id' => $jobRole->id,
            ],
            [
                'resume_id' => $resume->id,
                'match_percentage' => $matchPercentage,
                'matched_skills' => json_encode($matched),
                'missing_skills' => json_encode($missing),
            ]
        );

        $lead = $this->findOrCreateCandidateLead($resume->user_id, [
            'referral_source' => 'skill_gap',
            'lead_summary' => 'profile_active',
        ]);

        $lead->update([
            'skill_analysis_id' => $skillAnalysis->id,
            'job_role_id' => $jobRole->id,
            'employer_job_id' => null,
            'upskill_opportunity_id' => null,
            'match_percentage' => $matchPercentage,
            'missing_skills' => $missing,
            'intent_score' => $this->applyActivityIntent($lead, 5, $this->intentFromMatch($matchPercentage)),
            'status' => 'available',
            'lead_summary' => 'skill_gap',
        ]);

        return $lead->fresh();
    }

    public function recordEmployerJobLead(Resume $resume, EmployerJob $job, ?int $matchPercentage = null, ?string $source = null): Lead
    {
        $match = $matchPercentage ?? $this->computeEmployerJobMatchForResume($resume, $job);
        $summary = $source === 'job_application' ? 'job_application' : 'employer_job_interest';
        $boost = $source === 'job_application' ? 15 : 5;

        $lead = $this->findOrCreateCandidateLead($resume->user_id, [
            'referral_source' => $source,
            'lead_summary' => 'profile_active',
        ]);

        $lead->update([
            'skill_analysis_id' => null,
            'job_role_id' => null,
            'employer_job_id' => $job->id,
            'upskill_opportunity_id' => null,
            'match_percentage' => $match,
            'missing_skills' => null,
            'intent_score' => $this->applyActivityIntent($lead, $boost, $this->intentFromMatch($match)),
            'status' => 'available',
            'referral_source' => $source,
            'lead_summary' => $summary,
        ]);

        return $lead->fresh();
    }

    public function recordUpskillLead(int $candidateId, UpskillOpportunity $opportunity): Lead
    {
        $lead = $this->findOrCreateCandidateLead($candidateId, [
            'referral_source' => 'upskill_contact',
            'lead_summary' => 'profile_active',
        ]);

        $lead->update([
            'upskill_opportunity_id' => $opportunity->id,
            'intent_score' => $this->applyActivityIntent($lead, 10, 40),
            'status' => 'available',
            'referral_source' => 'upskill_contact',
            'lead_summary' => 'upskill_contact',
        ]);

        return $lead->fresh();
    }

    public function tagLatestLead(int $candidateId, array $where, string $source): void
    {
        $this->findCandidateLead($candidateId)?->update(['referral_source' => $source]);
    }

    public function recordGenericReferralLead(int $candidateId, string $source): Lead
    {
        $lead = $this->findOrCreateCandidateLead($candidateId, [
            'referral_source' => $source,
            'lead_summary' => 'profile_active',
        ]);

        $lead->update([
            'intent_score' => $this->applyActivityIntent($lead, 5),
            'referral_source' => $source,
            'lead_summary' => 'referral_intent_generic',
        ]);

        return $lead->fresh();
    }

    public function recordReferralLeadWithoutResume(int $candidateId, string $source, ?int $jobRoleId, ?int $employerJobId): Lead
    {
        $lead = $this->findOrCreateCandidateLead($candidateId, [
            'referral_source' => $source,
            'lead_summary' => 'profile_active',
        ]);

        $lead->update([
            'job_role_id' => $jobRoleId,
            'employer_job_id' => $employerJobId,
            'intent_score' => $this->applyActivityIntent($lead, 5),
            'referral_source' => $source,
            'lead_summary' => 'referral_intent_no_resume',
        ]);

        return $lead->fresh();
    }

    public function recordGuestReferral(string $source, ?int $jobRoleId, ?int $employerJobId): Lead
    {
        $meta = ['source' => $source];
        if (auth()->check()) {
            $meta['user_id'] = auth()->id();
            $u = auth()->user();
            $meta['account'] = $u->isCandidate() ? 'candidate' : ($u->isReferrer() ? 'referrer' : 'other');
        }

        return Lead::query()->create([
            'candidate_id' => null,
            'skill_analysis_id' => null,
            'job_role_id' => $jobRoleId,
            'employer_job_id' => $employerJobId,
            'upskill_opportunity_id' => null,
            'match_percentage' => null,
            'missing_skills' => null,
            'intent_score' => null,
            'status' => 'available',
            'referral_source' => $source,
            'lead_summary' => json_encode($meta),
        ]);
    }

    protected function findCandidateLead(int $candidateId): ?Lead
    {
        return Lead::query()
            ->where('candidate_id', $candidateId)
            ->orderBy('id')
            ->first();
    }

    protected function findOrCreateCandidateLead(int $candidateId, array $defaults = []): Lead
    {
        return $this->findCandidateLead($candidateId)
            ?? Lead::query()->create(array_merge([
                'candidate_id' => $candidateId,
                'skill_analysis_id' => null,
                'job_role_id' => null,
                'employer_job_id' => null,
                'upskill_opportunity_id' => null,
                'match_percentage' => null,
                'missing_skills' => null,
                'intent_score' => 10,
                'status' => 'available',
            ], $defaults));
    }

    /**
     * Increase intent on activity: never lower score; cap at 100.
     */
    protected function applyActivityIntent(Lead $lead, int $activityBoost, ?int $matchBasedIntent = null): int
    {
        $current = (int) ($lead->intent_score ?? 0);
        $fromMatch = $matchBasedIntent ?? 0;

        return min(100, max($current, $fromMatch) + $activityBoost);
    }

    protected function intentFromMatch(?int $matchPercentage): ?int
    {
        if ($matchPercentage === null) {
            return null;
        }

        return (int) min(100, max(0, round($matchPercentage * 0.85)));
    }

    /**
     * Same scoring as ResumeController::computeEmployerJobMatchForResume / job recommendations.
     */
    public function computeEmployerJobMatchForResume(Resume $resume, EmployerJob $job): int
    {
        $skills = $resume->getExtractedSkillsList();
        $summaryWords = [];
        if (! empty($resume->ai_summary)) {
            $summaryWords = array_filter(
                preg_split('/[\s,;.\-\/]+/', strip_tags($resume->ai_summary), -1, PREG_SPLIT_NO_EMPTY),
                fn ($w) => strlen($w) > 2
            );
            $summaryWords = array_map('strtolower', array_unique(array_slice($summaryWords, 0, 50)));
        }

        $jobText = strtolower(($job->title ?? '').' '.strip_tags($job->description ?? ''));
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
        if ($matchPercentage === 0 && $summaryWords !== []) {
            $summaryMatches = 0;
            foreach (array_slice($summaryWords, 0, 20) as $word) {
                if (str_contains($jobText, $word)) {
                    $summaryMatches++;
                }
            }
            $matchPercentage = (int) round(min(100, (count(array_slice($summaryWords, 0, 20)) > 0 ? ($summaryMatches / 20) * 100 : 0)));
        }

        return min(100, $matchPercentage);
    }
}
