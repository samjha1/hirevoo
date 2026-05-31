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
 */
class CandidateLeadService
{
    public function __construct(
        protected ResumeAnalysisService $resumeAnalysis,
    ) {
    }

    /**
     * First touch: candidate uploaded resume / has activity but no lead row yet.
     */
    public function ensureCandidateCrmLead(int $candidateId, string $source = 'resume_upload'): ?Lead
    {
        if (Lead::query()->where('candidate_id', $candidateId)->exists()) {
            return null;
        }

        return Lead::query()->create([
            'candidate_id' => $candidateId,
            'skill_analysis_id' => null,
            'job_role_id' => null,
            'employer_job_id' => null,
            'upskill_opportunity_id' => null,
            'match_percentage' => null,
            'missing_skills' => null,
            'intent_score' => null,
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

        return Lead::updateOrCreate(
            [
                'candidate_id' => $resume->user_id,
                'skill_analysis_id' => $skillAnalysis->id,
            ],
            [
                'job_role_id' => $jobRole->id,
                'employer_job_id' => null,
                'upskill_opportunity_id' => null,
                'match_percentage' => $matchPercentage,
                'missing_skills' => $missing,
                'intent_score' => $this->intentFromMatch($matchPercentage),
                'status' => 'available',
                'lead_summary' => 'skill_gap',
            ]
        );
    }

    public function recordEmployerJobLead(Resume $resume, EmployerJob $job, ?int $matchPercentage = null, ?string $source = null): Lead
    {
        $match = $matchPercentage ?? $this->computeEmployerJobMatchForResume($resume, $job);

        return Lead::updateOrCreate(
            [
                'candidate_id' => $resume->user_id,
                'employer_job_id' => $job->id,
            ],
            [
                'skill_analysis_id' => null,
                'job_role_id' => null,
                'upskill_opportunity_id' => null,
                'match_percentage' => $match,
                'missing_skills' => null,
                'intent_score' => $this->intentFromMatch($match),
                'status' => 'available',
                'referral_source' => $source,
                'lead_summary' => $source === 'job_application' ? 'job_application' : 'employer_job_interest',
            ]
        );
    }

    public function recordUpskillLead(int $candidateId, UpskillOpportunity $opportunity): Lead
    {
        return Lead::firstOrCreate(
            [
                'candidate_id' => $candidateId,
                'upskill_opportunity_id' => $opportunity->id,
            ],
            [
                'skill_analysis_id' => null,
                'job_role_id' => null,
                'employer_job_id' => null,
                'match_percentage' => null,
                'missing_skills' => null,
                'intent_score' => 40,
                'status' => 'available',
                'referral_source' => 'upskill_contact',
                'lead_summary' => 'upskill_contact',
            ]
        );
    }

    public function tagLatestLead(int $candidateId, array $where, string $source): void
    {
        $q = Lead::query()->where('candidate_id', $candidateId);
        foreach ($where as $column => $value) {
            $q->where($column, $value);
        }
        $lead = $q->latest('id')->first();
        $lead?->update(['referral_source' => $source]);
    }

    public function recordGenericReferralLead(int $candidateId, string $source): Lead
    {
        return Lead::query()->create([
            'candidate_id' => $candidateId,
            'skill_analysis_id' => null,
            'job_role_id' => null,
            'employer_job_id' => null,
            'upskill_opportunity_id' => null,
            'match_percentage' => null,
            'missing_skills' => null,
            'intent_score' => null,
            'status' => 'available',
            'referral_source' => $source,
            'lead_summary' => 'referral_intent_generic',
        ]);
    }

    public function recordReferralLeadWithoutResume(int $candidateId, string $source, ?int $jobRoleId, ?int $employerJobId): Lead
    {
        return Lead::query()->create([
            'candidate_id' => $candidateId,
            'skill_analysis_id' => null,
            'job_role_id' => $jobRoleId,
            'employer_job_id' => $employerJobId,
            'upskill_opportunity_id' => null,
            'match_percentage' => null,
            'missing_skills' => null,
            'intent_score' => null,
            'status' => 'available',
            'referral_source' => $source,
            'lead_summary' => 'referral_intent_no_resume',
        ]);
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
