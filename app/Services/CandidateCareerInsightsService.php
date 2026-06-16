<?php

namespace App\Services;

use App\Models\CandidateProfile;
use App\Models\EmployerJob;
use App\Models\JobRole;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CandidateCareerInsightsService
{
    public function __construct(
        protected ResumeAnalysisService $resumeAnalysis,
    ) {}

    /**
     * Cached career snapshot for dashboard and feature pages.
     *
     * @return array<string, mixed>
     */
    public function snapshot(User $user): array
    {
        $ttl = (int) config('hirevo_candidate_features.insights_cache_ttl', 1800);

        return Cache::remember($this->cacheKey($user), $ttl, fn () => $this->buildSnapshot($user));
    }

    /**
     * Lighter snapshot for dashboard — skips salary, assessments, and mock interviews.
     *
     * @return array<string, mixed>
     */
    public function dashboardSnapshot(User $user): array
    {
        $ttl = (int) config('hirevo_candidate_features.insights_cache_ttl', 1800);

        return Cache::remember($this->dashboardCacheKey($user), $ttl, fn () => $this->buildDashboardSnapshot($user));
    }

    public function forget(User $user): void
    {
        $stamp = $this->cacheStamp($user);
        Cache::forget('candidate_insights:'.$user->id.':'.$stamp);
        Cache::forget('candidate_insights_dash:'.$user->id.':'.$stamp);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSnapshot(User $user): array
    {
        $resume = $this->primaryResume($user);
        $profile = $user->candidateProfile;
        $targetRole = $this->resolveTargetRole($user, $resume);
        $skillGaps = $this->buildSkillGapAnalysis($user, $resume, $targetRole);
        $jobMatches = $this->buildPersonalizedJobMatches($user, $resume);
        $salary = $this->buildSalaryInsights($user, $resume, $targetRole, $profile);
        $assessments = $this->buildAssessmentPacks($resume, $targetRole, $skillGaps);
        $mockInterviews = $this->buildMockInterviewPack($user, $resume, $targetRole, $skillGaps);

        return [
            'resume' => $resume,
            'profile' => $profile,
            'target_role' => $targetRole,
            'skill_gaps' => $skillGaps,
            'job_matches' => $jobMatches,
            'salary' => $salary,
            'assessments' => $assessments,
            'mock_interviews' => $mockInterviews,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildDashboardSnapshot(User $user): array
    {
        $resume = $this->primaryResume($user);
        $targetRole = $this->resolveTargetRole($user, $resume);
        $skillGaps = $this->buildSkillGapAnalysis($user, $resume, $targetRole);
        $jobMatches = $this->buildPersonalizedJobMatches($user, $resume, [
            'goal_scan' => (int) config('hirevo_candidate_features.job_match_dashboard_goal_scan', 12),
            'employer_scan' => (int) config('hirevo_candidate_features.job_match_dashboard_employer_scan', 40),
            'employer_take' => (int) config('hirevo_candidate_features.job_match_dashboard_employer_take', 8),
            'page_limit' => (int) config('hirevo_candidate_features.job_match_dashboard_page_limit', 8),
        ]);

        return [
            'resume' => $resume,
            'target_role' => $targetRole,
            'skill_gaps' => $skillGaps,
            'job_matches' => $jobMatches,
        ];
    }

    public function primaryResume(User $user): ?Resume
    {
        return $user->resumes()->where('is_primary', true)->first()
            ?? $user->resumes()->orderByDesc('created_at')->first();
    }

    protected function cacheStamp(User $user): int
    {
        $resume = $this->primaryResume($user);
        $resumeStamp = $resume?->updated_at?->timestamp ?? 0;
        $profileStamp = $user->candidateProfile?->updated_at?->timestamp ?? 0;

        return max($resumeStamp, $profileStamp);
    }

    protected function cacheKey(User $user): string
    {
        return 'candidate_insights:'.$user->id.':'.$this->cacheStamp($user);
    }

    protected function dashboardCacheKey(User $user): string
    {
        return 'candidate_insights_dash:'.$user->id.':'.$this->cacheStamp($user);
    }

    protected function resolveTargetRole(User $user, ?Resume $resume): ?JobRole
    {
        $latestGoalApp = $user->jobApplications()
            ->with(['jobRole.requiredSkills'])
            ->orderByDesc('created_at')
            ->first();

        if ($latestGoalApp?->jobRole?->requiredSkills?->isNotEmpty()) {
            return $latestGoalApp->jobRole;
        }

        if (! $resume) {
            return null;
        }

        $topGoals = $this->resumeAnalysis->getMatchingJobGoalsForResume($resume, 1);
        $role = $topGoals[0]['job_role'] ?? null;
        if ($role instanceof JobRole) {
            $role->loadMissing('requiredSkills');
            if ($role->requiredSkills->isNotEmpty()) {
                return $role;
            }
        }

        return null;
    }

    /**
     * @return array{
     *     match_pct: int|null,
     *     matched: list<string>,
     *     gaps: list<string>,
     *     gap_details: list<array{skill: string, priority: int, impact: string, current_pct: int, target_pct: int, roles_count: int, roles_sample: list<string>, interview_tip: string}>,
     *     resume_skills: list<string>,
     *     role_title: string|null,
     *     role_url: string|null,
     *     source: string|null,
     *     summary: string|null,
     *     roles_scanned: int|null
     * }
     */
    public function buildSkillGapAnalysis(User $user, ?Resume $resume, ?JobRole $targetRole): array
    {
        $profileSkills = $user->candidateProfile?->skills;
        $resumeSkills = $this->resumeSkillsForDisplay($resume, $profileSkills);

        $empty = [
            'match_pct' => null,
            'matched' => [],
            'gaps' => [],
            'gap_details' => [],
            'resume_skills' => $resumeSkills,
            'role_title' => $targetRole?->title,
            'role_url' => $targetRole ? route('job-goal.show', $targetRole) : null,
            'source' => null,
            'summary' => null,
            'roles_scanned' => null,
        ];

        if (! $resume) {
            return $empty;
        }

        if ($targetRole && $targetRole->requiredSkills->isNotEmpty()) {
            return $this->skillGapsForTargetRole($resume, $targetRole, $profileSkills, $resumeSkills);
        }

        return $this->skillGapsFromResumeMarketScan($resume, $profileSkills, $resumeSkills);
    }

    /**
     * @return array<string, mixed>
     */
    protected function skillGapsForTargetRole(Resume $resume, JobRole $targetRole, ?string $profileSkills, array $resumeSkills): array
    {
        $required = $this->resumeAnalysis->orderedRequiredSkillLabels($targetRole);
        $coverage = $this->resumeAnalysis->matchResumeToRequiredSkillNames(
            $resume,
            $required,
            $profileSkills
        );

        $matchPct = (int) ($coverage['match_pct'] ?? 0);
        $gaps = $coverage['gaps_display'] ?? [];
        $matched = $coverage['matched_display'] ?? [];
        $gapDetails = $this->buildGapDetails($gaps, $matchPct, function (string $skill) use ($targetRole): array {
            return [
                'roles_count' => 1,
                'roles_sample' => [$targetRole->title],
            ];
        });

        $gapCount = count($gaps);

        return [
            'match_pct' => $matchPct,
            'matched' => $matched,
            'gaps' => $gaps,
            'gap_details' => $gapDetails,
            'resume_skills' => $resumeSkills,
            'role_title' => $targetRole->title,
            'role_url' => route('job-goal.show', $targetRole),
            'source' => 'resume_role_match',
            'summary' => $gapCount > 0
                ? "Your resume is missing {$gapCount} skill(s) employers look for in {$targetRole->title}. Closing these gaps can improve ATS pass-through and interview shortlists."
                : "Strong skill match for {$targetRole->title}. Focus on mock interviews and applications.",
            'roles_scanned' => 1,
        ];
    }

    /**
     * Aggregate missing skills across top job goals matched to the resume.
     *
     * @return array<string, mixed>
     */
    protected function skillGapsFromResumeMarketScan(Resume $resume, ?string $profileSkills, array $resumeSkills): array
    {
        $rows = $this->resumeAnalysis->getMatchingJobGoalsForResume($resume, 12);
        $gapMap = [];
        $matchedMap = [];
        $bestMatch = 0;
        $bestRole = null;
        $rolesScanned = 0;

        foreach ($rows as $row) {
            $role = $row['job_role'] ?? null;
            if (! $role instanceof JobRole) {
                continue;
            }

            $required = $this->resumeAnalysis->orderedRequiredSkillLabels($role);
            if ($required === []) {
                continue;
            }

            $rolesScanned++;
            $coverage = $this->resumeAnalysis->matchResumeToRequiredSkillNames($resume, $required, $profileSkills);
            $pct = (int) ($coverage['match_pct'] ?? 0);

            if ($pct > $bestMatch) {
                $bestMatch = $pct;
                $bestRole = $role;
            }

            foreach ($coverage['matched_display'] ?? [] as $skill) {
                $matchedMap[mb_strtolower($skill)] = $skill;
            }

            foreach ($coverage['gaps_display'] ?? [] as $gap) {
                $key = mb_strtolower($gap);
                if (! isset($gapMap[$key])) {
                    $gapMap[$key] = ['skill' => $gap, 'roles' => [], 'count' => 0];
                }
                $gapMap[$key]['count']++;
                if (count($gapMap[$key]['roles']) < 3 && ! in_array($role->title, $gapMap[$key]['roles'], true)) {
                    $gapMap[$key]['roles'][] = $role->title;
                }
            }
        }

        uasort($gapMap, fn (array $a, array $b): int => $b['count'] <=> $a['count'] ?: strcmp($a['skill'], $b['skill']));

        $gaps = array_values(array_map(fn (array $item): string => $item['skill'], $gapMap));
        $gapDetails = $this->buildGapDetails($gaps, $bestMatch, function (string $skill) use ($gapMap): array {
            $key = mb_strtolower($skill);
            $item = $gapMap[$key] ?? ['count' => 0, 'roles' => []];

            return [
                'roles_count' => (int) $item['count'],
                'roles_sample' => $item['roles'],
            ];
        });

        $gapCount = count($gaps);
        $roleTitle = $bestRole?->title ?? ($resumeSkills !== [] ? 'Roles matching your resume' : null);

        return [
            'match_pct' => $rolesScanned > 0 ? $bestMatch : null,
            'matched' => array_values($matchedMap),
            'gaps' => $gaps,
            'gap_details' => $gapDetails,
            'resume_skills' => $resumeSkills,
            'role_title' => $roleTitle,
            'role_url' => $bestRole ? route('job-goal.show', $bestRole) : null,
            'source' => 'resume_market_scan',
            'summary' => $gapCount > 0
                ? "We scanned {$rolesScanned} roles that fit your resume. {$gapCount} skill(s) are missing most often — adding them can unlock more interview calls."
                : ($rolesScanned > 0
                    ? 'Your resume skills align well with matching roles. Practice interviews and apply to open roles.'
                    : 'Add skills to your resume or profile so we can compare you against job requirements.'),
            'roles_scanned' => $rolesScanned,
        ];
    }

    /**
     * @param  list<string>  $gaps
     * @param  callable(string): array{roles_count: int, roles_sample: list<string>}  $roleMeta
     * @return list<array{skill: string, priority: int, impact: string, current_pct: int, target_pct: int, roles_count: int, roles_sample: list<string>, interview_tip: string}>
     */
    protected function buildGapDetails(array $gaps, int $matchPct, callable $roleMeta): array
    {
        $gapDetails = [];

        foreach (array_values($gaps) as $index => $skill) {
            $meta = $roleMeta($skill);
            $rolesCount = max(1, (int) ($meta['roles_count'] ?? 1));
            $rolesSample = $meta['roles_sample'] ?? [];

            if ($rolesCount >= 4) {
                $priority = 1;
                $impact = "Critical — required in {$rolesCount} matching roles; often filtered in ATS before interviews.";
            } elseif ($rolesCount >= 2) {
                $priority = 2;
                $impact = "High impact — missing in {$rolesCount} roles you could qualify for.";
            } else {
                $priority = $index < 3 ? 1 : ($index < 6 ? 2 : 3);
                $impact = match ($priority) {
                    1 => 'High — often required in screening & ATS',
                    2 => 'Medium — strengthens shortlist chances',
                    default => 'Good to have — differentiates you in interviews',
                };
            }

            $currentPct = max(0, min(45, $matchPct > 0 ? max(10, 55 - (12 * $index)) : max(5, 25 - (8 * $index))));
            $targetPct = min(95, 72 + (4 * max(0, 3 - min($index, 3))));

            $rolesLabel = $rolesSample !== []
                ? implode(', ', array_slice($rolesSample, 0, 2)).($rolesCount > 2 ? ' +'.($rolesCount - 2).' more' : '')
                : 'matching roles';

            $gapDetails[] = [
                'skill' => $skill,
                'priority' => $priority,
                'impact' => $impact,
                'current_pct' => $currentPct,
                'target_pct' => $targetPct,
                'roles_count' => $rolesCount,
                'roles_sample' => $rolesSample,
                'interview_tip' => "Add {$skill} to your resume (projects, certifications, or hands-on examples) — demanded by {$rolesLabel}.",
            ];
        }

        return $gapDetails;
    }

    /**
     * @return list<string>
     */
    protected function resumeSkillsForDisplay(?Resume $resume, ?string $profileSkills): array
    {
        $seen = [];
        $out = [];

        $add = function (string $raw) use (&$seen, &$out): void {
            $label = trim($raw);
            if ($label === '') {
                return;
            }
            $key = mb_strtolower($label);
            if (isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;
            $out[] = $label;
        };

        if ($resume) {
            if (is_array($resume->extracted_skills)) {
                foreach ($resume->extracted_skills as $skill) {
                    if (is_string($skill)) {
                        $add($skill);
                    }
                }
            }
            foreach ($resume->getExtractedSkillsList() as $skill) {
                $add($skill);
            }
        }

        if (is_string($profileSkills) && trim($profileSkills) !== '') {
            foreach (preg_split('/[,;|]+/', $profileSkills) ?: [] as $part) {
                $add((string) $part);
            }
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    /**
     * @param  array<string, int>|null  $limits  Optional overrides: goal_scan, employer_scan, employer_take, page_limit
     * @return list<array<string, mixed>>
     */
    public function buildPersonalizedJobMatches(User $user, ?Resume $resume, ?array $limits = null): array
    {
        if (! $resume) {
            return [];
        }

        $strongMinPct = (int) config('hirevo_candidate_features.job_match_min_pct', 45);
        $includeMinPct = (int) config('hirevo_candidate_features.job_match_include_min_pct', 15);
        $pageLimit = (int) ($limits['page_limit'] ?? config('hirevo_candidate_features.job_match_page_limit', 30));
        $goalScan = (int) ($limits['goal_scan'] ?? config('hirevo_candidate_features.job_match_goal_scan', 35));
        $employerScan = (int) ($limits['employer_scan'] ?? config('hirevo_candidate_features.job_match_employer_scan', 150));
        $employerTake = (int) ($limits['employer_take'] ?? config('hirevo_candidate_features.job_match_employer_take', 25));

        $profileSkills = $user->candidateProfile?->skills;
        $appliedJobIds = $user->employerJobApplications()->pluck('employer_job_id')->all();
        $matches = [];

        foreach ($this->resumeAnalysis->getMatchingJobGoalsForResume($resume, $goalScan) as $row) {
            $role = $row['job_role'] ?? null;
            if (! $role instanceof JobRole) {
                continue;
            }

            $required = $this->resumeAnalysis->orderedRequiredSkillLabels($role);
            $missing = [];
            $pct = (int) ($row['match_percentage'] ?? 0);

            if ($required !== []) {
                $cov = $this->resumeAnalysis->matchResumeToRequiredSkillNames($resume, $required, $profileSkills);
                $pct = (int) ($cov['match_pct'] ?? $pct);
                $missing = array_slice($cov['gaps_display'] ?? [], 0, 5);
            }

            if ($pct < $includeMinPct) {
                continue;
            }

            $matches[] = $this->formatJobMatchRow([
                'type' => 'goal',
                'title' => $role->title,
                'company' => 'Job goal',
                'location' => $role->sector ?: 'Skill-based match',
                'experience' => 'Varies',
                'salary' => '—',
                'match' => $pct,
                'missing_skills' => $missing,
                'url' => route('job-goal.show', $role),
            ], $strongMinPct);
        }

        $jobs = EmployerJob::query()
            ->where('status', 'active')
            ->when($appliedJobIds !== [], fn ($q) => $q->whereNotIn('id', $appliedJobIds))
            ->with(['user.referrerProfile'])
            ->orderByDesc('created_at')
            ->limit($employerScan)
            ->get();

        if ($jobs->isNotEmpty()) {
            $resumeText = $this->resumeAnalysis->getResumePlainTextForMatching($resume);
            $scoredJobs = [];

            foreach ($jobs as $job) {
                $req = is_array($job->required_skills) ? $job->required_skills : [];
                $req = array_values(array_filter(array_map(
                    static fn ($s) => is_string($s) ? trim($s) : '',
                    $req
                ), static fn (string $s): bool => $s !== ''));

                $pct = 0;
                $missing = [];

                if ($resumeText !== '') {
                    $rb = $this->resumeAnalysis->getEmployerJobMatchRuleBased(
                        $resumeText,
                        (string) ($job->title ?? ''),
                        strip_tags((string) ($job->description ?? '')),
                        array_slice($req, 0, 12)
                    );
                    $pct = (int) ($rb['score'] ?? 0);
                }

                if ($req !== []) {
                    $cov = $this->resumeAnalysis->matchResumeToRequiredSkillNames(
                        $resume,
                        array_slice($req, 0, 12),
                        $profileSkills
                    );
                    if ($cov['match_pct'] > 0) {
                        $pct = max($pct, (int) $cov['match_pct']);
                    }
                    $missing = array_slice($cov['gaps_display'] ?? [], 0, 5);
                }

                if ($pct < $includeMinPct) {
                    continue;
                }

                $company = $job->company_name ?? ($job->user?->referrerProfile?->company_name ?? 'Company');
                $scoredJobs[] = array_merge(
                    $this->formatJobMatchRow([
                        'type' => 'employer',
                        'title' => $job->title,
                        'company' => $company,
                        'location' => $job->formatted_location ?? ($job->location ?? 'Remote'),
                        'experience' => $job->experience_years !== null
                            ? $job->experience_years.'+ yrs'
                            : 'Not specified',
                        'salary' => $job->formatted_salary_summary ?? '—',
                        'match' => $pct,
                        'missing_skills' => $missing,
                        'url' => route('job-openings.apply', $job),
                    ], $strongMinPct),
                    ['_sort' => $pct]
                );
            }

            usort($scoredJobs, fn ($a, $b) => ($b['_sort'] ?? 0) <=> ($a['_sort'] ?? 0));
            foreach (array_slice($scoredJobs, 0, $employerTake) as $row) {
                unset($row['_sort']);
                $matches[] = $row;
            }
        }

        usort($matches, fn ($a, $b) => ($b['match'] ?? 0) <=> ($a['match'] ?? 0));

        return array_slice($matches, 0, $pageLimit);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function formatJobMatchRow(array $row, int $strongMinPct): array
    {
        $match = (int) ($row['match'] ?? 0);
        $moderateMin = max(15, (int) round($strongMinPct * 0.65));

        $tier = 'stretch';
        if ($match >= $strongMinPct) {
            $tier = 'strong';
        } elseif ($match >= $moderateMin) {
            $tier = 'moderate';
        }

        $row['match_tier'] = $tier;
        $row['match_label'] = match ($tier) {
            'strong' => 'Strong match',
            'moderate' => 'Good fit',
            default => 'Related role',
        };

        return $row;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSalaryInsights(User $user, ?Resume $resume, ?JobRole $targetRole, ?CandidateProfile $profile): array
    {
        $headline = trim((string) ($profile?->headline ?? ''));
        $preferredRole = trim((string) ($profile?->preferred_job_role ?? ''));
        $roleTitle = $targetRole?->title ?? ($headline !== '' ? $headline : $preferredRole);
        if ($roleTitle === '') {
            $roleTitle = 'your role';
        }

        $experienceYears = (int) ($profile?->experience_years ?? 0);
        $location = trim((string) ($profile?->location ?? $profile?->preferred_job_location ?? 'India'));
        $city = trim(explode(',', $location)[0] ?? $location);

        $band = $this->resolveSalaryBand($roleTitle);
        $tier = $experienceYears <= 1 ? 'fresher' : ($experienceYears <= 5 ? 'mid' : 'senior');
        $market = $band[$tier];
        $marketMin = (int) $market['min'];
        $marketMax = (int) $market['max'];
        $marketMid = (int) round(($marketMin + $marketMax) / 2);

        $expectedRaw = $profile?->expected_salary ?? $profile?->formattedExpectedSalary();
        $expectedLpa = $this->parseSalaryToLpa($expectedRaw);
        $comparison = null;
        if ($expectedLpa !== null) {
            if ($expectedLpa < $marketMin) {
                $comparison = 'below_market';
                $comparisonLabel = 'Below typical market — room to negotiate up as you close skill gaps.';
            } elseif ($expectedLpa > $marketMax) {
                $comparison = 'above_market';
                $comparisonLabel = 'Above typical band — ensure senior scope or niche skills justify it.';
            } else {
                $comparison = 'in_range';
                $comparisonLabel = 'Within market range for your experience and role.';
            }
        } else {
            $comparisonLabel = 'Add expected salary on your profile for a personalized comparison.';
        }

        $premiumSkills = $this->premiumSkillsForRole($roleTitle);
        $gapSkills = [];
        if ($resume && $targetRole) {
            $required = $this->resumeAnalysis->orderedRequiredSkillLabels($targetRole);
            $cov = $this->resumeAnalysis->matchResumeToRequiredSkillNames(
                $resume,
                $required,
                $profile?->skills
            );
            $gapSkills = array_slice($cov['gaps_display'] ?? [], 0, 4);
        }

        return [
            'role_title' => $roleTitle,
            'city' => $city,
            'experience_years' => $experienceYears,
            'experience_tier' => $tier,
            'market_min_lpa' => $marketMin,
            'market_max_lpa' => $marketMax,
            'market_mid_lpa' => $marketMid,
            'expected_lpa' => $expectedLpa,
            'comparison' => $comparison,
            'comparison_label' => $comparisonLabel,
            'premium_skills' => $premiumSkills,
            'skills_to_increase_pay' => $gapSkills,
            'tips' => [
                'Research ₹'.$marketMin.'–'.$marketMax.' LPA benchmarks for '.$roleTitle.' in '.$city.'.',
                'Highlight quantified achievements (%, revenue, time saved) in interviews.',
                'Closing top skill gaps can move you toward the upper band within 6–12 months.',
            ],
        ];
    }

    /**
     * @return list<array{skill: string, title: string, questions: list<array{question: string, options: list<string>, answer: int}>, question_count: int}>
     */
    public function buildAssessmentPacks(?Resume $resume, ?JobRole $targetRole, array $skillGaps): array
    {
        $skills = $this->skillsForAssessments($resume, $targetRole, $skillGaps);
        $pools = config('hirevo_candidate_features.assessment_questions', []);
        $packs = [];

        foreach (array_slice($skills, 0, 5) as $skill) {
            $questions = $this->questionsForSkill($skill, $pools);
            if ($questions === []) {
                continue;
            }
            $packs[] = [
                'skill' => $skill,
                'title' => $skill.' readiness check',
                'questions' => $questions,
                'question_count' => count($questions),
            ];
        }

        if ($packs === [] && $targetRole) {
            $packs[] = [
                'skill' => 'Communication',
                'title' => 'Professional readiness check',
                'questions' => $pools['Communication'] ?? [],
                'question_count' => count($pools['Communication'] ?? []),
            ];
        }

        return $packs;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildMockInterviewPack(User $user, ?Resume $resume, ?JobRole $targetRole, array $skillGaps): array
    {
        $packs = config('hirevo_candidate_features.mock_interview_packs', []);
        $roleTitle = $targetRole?->title ?? ($user->candidateProfile?->headline ?? 'your target role');
        $readiness = $this->estimateInterviewReadiness($user, $resume, $skillGaps);

        $technical = $packs['technical'] ?? [];
        if ($targetRole && str_contains(mb_strtolower($roleTitle), 'recruit')) {
            $technical = array_merge($packs['hr_screening'] ?? [], array_slice($technical, 0, 2));
        }

        return [
            'role_title' => $roleTitle,
            'readiness_score' => $readiness['score'],
            'readiness_label' => $readiness['label'],
            'sections' => [
                ['key' => 'behavioral', 'title' => 'Behavioral (STAR)', 'icon' => 'mdi-account-voice', 'questions' => $packs['behavioral'] ?? []],
                ['key' => 'technical', 'title' => 'Role & technical', 'icon' => 'mdi-code-tags', 'questions' => $technical],
                ['key' => 'hr_screening', 'title' => 'HR & screening', 'icon' => 'mdi-briefcase-outline', 'questions' => $packs['hr_screening'] ?? []],
            ],
            'checklist' => [
                'Research the company and role (10 min)',
                'Prepare 3 STAR stories from your resume',
                'Practice answers out loud — record yourself',
                'Prepare 2 thoughtful questions for the interviewer',
                'Close top skill gaps from your analysis',
            ],
        ];
    }

    /**
     * @return list<array{skill: string, current: int, recommended: int}>
     */
    public function skillGapChartFromAnalysis(array $skillGaps): array
    {
        $details = $skillGaps['gap_details'] ?? [];
        if ($details !== []) {
            return array_map(fn (array $row) => [
                'skill' => $row['skill'],
                'current' => (int) $row['current_pct'],
                'recommended' => (int) $row['target_pct'],
            ], array_slice($details, 0, 6));
        }

        $matched = $skillGaps['matched'] ?? [];
        $matchPct = (int) ($skillGaps['match_pct'] ?? 55);
        $items = [];
        foreach (array_slice($matched, 0, 4) as $skill) {
            $items[] = [
                'skill' => $skill,
                'current' => min(90, $matchPct + 5),
                'recommended' => min(95, $matchPct + 20),
            ];
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    protected function skillsForAssessments(?Resume $resume, ?JobRole $targetRole, array $skillGaps): array
    {
        $skills = [];
        foreach ($skillGaps['gaps'] ?? [] as $skill) {
            $skills[] = $skill;
        }
        if ($resume && is_array($resume->extracted_skills)) {
            foreach (array_slice($resume->extracted_skills, 0, 6) as $skill) {
                if (is_string($skill) && trim($skill) !== '') {
                    $skills[] = trim($skill);
                }
            }
        }
        if ($targetRole) {
            foreach ($this->resumeAnalysis->orderedRequiredSkillLabels($targetRole) as $label) {
                $skills[] = $label;
            }
        }

        $unique = [];
        $seen = [];
        foreach ($skills as $skill) {
            $key = mb_strtolower(trim($skill));
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = trim($skill);
        }

        return $unique;
    }

    /**
     * @param  array<string, list<array{question: string, options: list<string>, answer: int}>>  $pools
     * @return list<array{question: string, options: list<string>, answer: int}>
     */
    protected function questionsForSkill(string $skill, array $pools): array
    {
        foreach ($pools as $key => $questions) {
            if (strcasecmp($key, $skill) === 0 || str_contains(mb_strtolower($skill), mb_strtolower($key))) {
                return $questions;
            }
        }

        foreach ($pools as $key => $questions) {
            if (str_contains(mb_strtolower($key), mb_strtolower($skill))
                || str_contains(mb_strtolower($skill), mb_strtolower($key))) {
                return $questions;
            }
        }

        return [];
    }

    /**
     * @return array{fresher: array{min: int, max: int}, mid: array{min: int, max: int}, senior: array{min: int, max: int}}
     */
    protected function resolveSalaryBand(string $roleTitle): array
    {
        $normalized = mb_strtolower($roleTitle);
        foreach (config('hirevo_candidate_features.salary_bands', []) as $band) {
            foreach ($band['keywords'] ?? [] as $keyword) {
                if (str_contains($normalized, mb_strtolower($keyword))) {
                    return [
                        'fresher' => $band['fresher'],
                        'mid' => $band['mid'],
                        'senior' => $band['senior'],
                    ];
                }
            }
        }

        return config('hirevo_candidate_features.default_salary_band', [
            'fresher' => ['min' => 2, 'max' => 6],
            'mid' => ['min' => 6, 'max' => 14],
            'senior' => ['min' => 14, 'max' => 28],
        ]);
    }

    protected function parseSalaryToLpa(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $text = mb_strtolower(trim((string) $value));
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:lpa|lakh)/i', $text, $m)) {
            return (float) $m[1];
        }
        if (preg_match('/(\d+(?:\.\d+)?)/', $text, $m)) {
            $num = (float) $m[1];
            if ($num > 100000) {
                return round($num / 100000, 1);
            }
            if ($num > 1000) {
                return round($num / 100000, 1);
            }

            return $num;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function premiumSkillsForRole(string $roleTitle): array
    {
        $normalized = mb_strtolower($roleTitle);
        if (str_contains($normalized, 'data') || str_contains($normalized, 'ml')) {
            return ['Python', 'SQL', 'Machine Learning', 'Cloud (AWS/GCP)'];
        }
        if (str_contains($normalized, 'recruit') || str_contains($normalized, 'hr')) {
            return ['ATS tools', 'LinkedIn Recruiter', 'Boolean search', 'Stakeholder management'];
        }
        if (str_contains($normalized, 'sales')) {
            return ['CRM (Salesforce)', 'Negotiation', 'Pipeline management', 'B2B outreach'];
        }

        return ['Cloud', 'System design', 'Leadership', 'Communication'];
    }

    /**
     * @return array{score: int, label: string}
     */
    protected function estimateInterviewReadiness(User $user, ?Resume $resume, array $skillGaps): array
    {
        $profilePct = CandidateProfile::completionStats($user->candidateProfile, $user)['percent'] ?? 0;
        $matchPct = (int) ($skillGaps['match_pct'] ?? 50);
        $hasResume = $resume !== null;
        $interviewCount = $user->employerJobApplications()->where('status', 'interviewed')->count();

        $score = (int) min(95, max(25,
            ($matchPct * 0.45)
            + ($profilePct * 0.25)
            + ($hasResume ? 15 : 0)
            + min(15, $interviewCount * 5)
        ));

        $label = match (true) {
            $score >= 75 => 'Interview ready',
            $score >= 55 => 'Almost ready — practice mock questions',
            default => 'Build skills & profile first',
        };

        return ['score' => $score, 'label' => $label];
    }
}
