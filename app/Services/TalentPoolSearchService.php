<?php

namespace App\Services;

use App\Models\CandidateProfile;
use App\Models\EmployerTalentPoolAction;
use App\Models\Resume;
use App\Models\TalentPoolCandidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TalentPoolSearchService
{
    public const SOURCE_VERIFIED = EmployerTalentPoolAction::SOURCE_VERIFIED;

    public const SOURCE_TALENT_POOL = EmployerTalentPoolAction::SOURCE_TALENT_POOL;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     items: Collection<int, array<string, mixed>>,
     *     paginator: Paginator,
     *     active_filter_count: int
     * }
     */
    public function search(int $employerUserId, array $filters = [], int $perPage = 20, int $page = 1, bool $withFacets = false): array
    {
        $perPage = max(10, min(30, $perPage));
        $page = max(1, $page);

        if (! $this->hasSearchCriteria($filters)) {
            return [
                'items' => collect(),
                'paginator' => new Paginator(collect(), $perPage, $page, [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]),
                'active_filter_count' => $this->countActiveFilters($filters),
                'facets' => $withFacets ? ['locations' => [], 'education' => [], 'experience' => []] : null,
                'total_count' => 0,
                'requires_search' => true,
            ];
        }

        $offset = ($page - 1) * $perPage;
        $rows = $this->fetchUnionPage($filters, $offset, $perPage + 1);
        $hasMore = $rows->count() > $perPage;
        if ($hasMore) {
            $rows = $rows->take($perPage);
        }

        $items = $this->hydrateUnionRows($rows);
        $actionMap = $this->actionMapForEmployer($employerUserId, $items);
        $items = $items->map(function (array $row) use ($actionMap) {
            $key = $row['source'].':'.$row['source_id'];
            $action = $actionMap[$key] ?? null;
            $row['is_saved'] = (bool) ($action['is_saved'] ?? false);
            $row['is_shortlisted'] = (bool) ($action['is_shortlisted'] ?? false);

            return $row;
        });

        $paginator = new Paginator(
            $items,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
        if ($hasMore) {
            $paginator->hasMorePagesWhen(true);
        }

        $result = [
            'items' => $items,
            'paginator' => $paginator,
            'active_filter_count' => $this->countActiveFilters($filters),
            'total_count' => $this->countMatchingCandidates($filters),
            'requires_search' => false,
        ];

        if ($withFacets) {
            $result['facets'] = $this->filterFacets($filters);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function countMatchingCandidates(array $filters): int
    {
        if (! $this->hasSearchCriteria($filters)) {
            return 0;
        }

        $verified = $this->verifiedCandidatesQuery($filters)->select('users.id');
        $talent = $this->talentPoolCandidatesQuery($filters)->select('talent_pool_candidates.id');

        return (int) DB::query()
            ->fromSub($verified->unionAll($talent), 'merged_candidates')
            ->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function hasSearchCriteria(array $filters): bool
    {
        $minLen = (int) config('hirevo_plans.min_search_length', 2);
        if (mb_strlen(trim((string) ($filters['q'] ?? ''))) >= $minLen) {
            return true;
        }
        if ($this->parseListFilter($filters['skills'] ?? '') !== []) {
            return true;
        }
        if ($this->selectedLocations($filters) !== []) {
            return true;
        }
        if (trim((string) ($filters['education'] ?? '')) !== '') {
            return true;
        }
        if (($filters['experience_min'] ?? '') !== '' || ($filters['experience_max'] ?? '') !== '') {
            return true;
        }
        if (filter_var($filters['saved_only'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }
        if (filter_var($filters['shortlisted_only'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{locations: list<array{label: string, count: int}>, education: list<array{label: string, count: int}>, experience: list<array{label: string, min: int|null, max: int|null, count: int}>}
     */
    public function filterFacets(array $filters): array
    {
        $forLocations = $filters;
        unset($forLocations['location'], $forLocations['locations']);

        $forEducation = $filters;
        unset($forEducation['education']);

        $forExperience = $filters;
        unset($forExperience['experience_min'], $forExperience['experience_max']);

        return [
            'locations' => $this->aggregateLocationFacets($forLocations),
            'education' => $this->aggregateEducationFacets($forEducation),
            'experience' => $this->aggregateExperienceFacets($forExperience),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function countActiveFilters(array $filters): int
    {
        $count = 0;
        if (trim((string) ($filters['q'] ?? '')) !== '') {
            $count++;
        }
        if ($this->parseListFilter($filters['skills'] ?? '') !== []) {
            $count++;
        }
        if ($this->selectedLocations($filters) !== []) {
            $count++;
        }
        if (trim((string) ($filters['education'] ?? '')) !== '') {
            $count++;
        }
        if (($filters['experience_min'] ?? '') !== '' || ($filters['experience_max'] ?? '') !== '') {
            $count++;
        }
        if (filter_var($filters['saved_only'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $count++;
        }
        if (filter_var($filters['shortlisted_only'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $count++;
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<string>
     */
    public function selectedLocations(array $filters): array
    {
        $locations = $filters['locations'] ?? [];
        if (is_string($locations) && $locations !== '') {
            $locations = [$locations];
        }
        if (! is_array($locations)) {
            $locations = [];
        }
        $locations = array_values(array_filter(array_map(fn ($s) => trim((string) $s), $locations)));
        if ($locations === [] && trim((string) ($filters['location'] ?? '')) !== '') {
            $locations = [trim((string) $filters['location'])];
        }

        return $locations;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, object{source: string, source_id: int}>
     */
    protected function fetchUnionPage(array $filters, int $offset, int $limit): Collection
    {
        $verified = $this->verifiedCandidatesQuery($filters)
            ->select([
                'users.id',
                DB::raw("'".self::SOURCE_VERIFIED."' as candidate_source"),
                DB::raw('0 as source_priority'),
                'users.updated_at as sort_at',
            ]);

        $talent = $this->talentPoolCandidatesQuery($filters)
            ->select([
                'talent_pool_candidates.id',
                DB::raw("'".self::SOURCE_TALENT_POOL."' as candidate_source"),
                DB::raw('1 as source_priority'),
                'talent_pool_candidates.created_at as sort_at',
            ]);

        $union = $verified->unionAll($talent);

        return DB::query()
            ->fromSub($union, 'merged_candidates')
            ->orderBy('source_priority')
            ->orderByDesc('sort_at')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    protected function hydrateUnionRows(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        $verifiedIds = $rows->where('candidate_source', self::SOURCE_VERIFIED)->pluck('id')->all();
        $talentIds = $rows->where('candidate_source', self::SOURCE_TALENT_POOL)->pluck('id')->all();

        $verifiedMap = collect();
        if ($verifiedIds !== []) {
            $verifiedMap = User::query()
                ->whereIn('id', $verifiedIds)
                ->with(['candidateProfile', 'resumes' => fn ($q) => $q->orderByDesc('is_primary')->orderByDesc('id')])
                ->get()
                ->keyBy('id');
        }

        $talentMap = collect();
        if ($talentIds !== []) {
            $talentMap = TalentPoolCandidate::query()
                ->whereIn('id', $talentIds)
                ->get()
                ->keyBy('id');
        }

        return $rows->map(function ($row) use ($verifiedMap, $talentMap) {
            if ($row->candidate_source === self::SOURCE_VERIFIED) {
                $user = $verifiedMap->get($row->id);

                return $user ? $this->normalizeVerified($user) : null;
            }

            $candidate = $talentMap->get($row->id);

            return $candidate ? $this->normalizeTalentPool($candidate) : null;
        })->filter()->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function details(string $source, int $sourceId): ?array
    {
        if ($source === self::SOURCE_VERIFIED) {
            $user = User::query()
                ->where('role', 'candidate')
                ->where('status', 'active')
                ->whereHas('candidateProfile')
                ->with(['candidateProfile', 'resumes' => fn ($q) => $q->orderByDesc('is_primary')->orderByDesc('id')])
                ->find($sourceId);

            return $user ? $this->normalizeVerified($user, detailed: true) : null;
        }

        if ($source === self::SOURCE_TALENT_POOL) {
            $candidate = TalentPoolCandidate::query()->discoverable()->find($sourceId);

            return $candidate ? $this->normalizeTalentPool($candidate, detailed: true) : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function verifiedCandidatesQuery(array $filters): Builder
    {
        $query = User::query()
            ->where('role', 'candidate')
            ->where('status', 'active')
            ->whereHas('candidateProfile');

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function (Builder $outer) use ($like, $q) {
                $outer->where('users.name', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('users.phone', 'like', $like)
                    ->orWhereHas('candidateProfile', function (Builder $profile) use ($like) {
                        $profile->where('headline', 'like', $like)
                            ->orWhere('location', 'like', $like)
                            ->orWhere('preferred_job_location', 'like', $like)
                            ->orWhere('education', 'like', $like)
                            ->orWhere('skills', 'like', $like)
                            ->orWhere('bio_summary', 'like', $like)
                            ->orWhere('career_objective', 'like', $like)
                            ->orWhere('current_company', 'like', $like);
                    });
            });
        }

        $skills = $this->parseListFilter($filters['skills'] ?? '');
        if ($skills !== []) {
            $query->whereHas('candidateProfile', function (Builder $profile) use ($skills) {
                $profile->where(function (Builder $skillQuery) use ($skills) {
                    foreach ($skills as $skill) {
                        $skillQuery->orWhere('skills', 'like', '%'.$skill.'%');
                    }
                });
            });
        }

        $this->applyVerifiedLocationFilter($query, $filters);

        $education = trim((string) ($filters['education'] ?? ''));
        if ($education !== '') {
            $query->whereHas('candidateProfile', function (Builder $profile) use ($education) {
                $profile->where('education', 'like', '%'.$education.'%');
            });
        }

        $this->applyVerifiedExperienceFilter($query, $filters);

        $savedOnly = filter_var($filters['saved_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $shortlistedOnly = filter_var($filters['shortlisted_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($savedOnly || $shortlistedOnly) {
            $employerId = (int) ($filters['employer_user_id'] ?? 0);
            $query->whereIn('users.id', function ($sub) use ($employerId, $savedOnly, $shortlistedOnly) {
                $sub->select('candidate_ref_id')
                    ->from('employer_talent_pool_actions')
                    ->where('employer_user_id', $employerId)
                    ->where('candidate_source', self::SOURCE_VERIFIED)
                    ->when($savedOnly, fn ($q) => $q->where('is_saved', true))
                    ->when($shortlistedOnly, fn ($q) => $q->where('is_shortlisted', true));
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function talentPoolCandidatesQuery(array $filters): Builder
    {
        $query = TalentPoolCandidate::query()->discoverable();

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function (Builder $outer) use ($like) {
                $outer->where('full_name', 'like', $like)
                    ->orWhere('title', 'like', $like)
                    ->orWhere('location', 'like', $like)
                    ->orWhere('education', 'like', $like)
                    ->orWhere('skills', 'like', $like)
                    ->orWhere('profile_summary', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        $skills = $this->parseListFilter($filters['skills'] ?? '');
        if ($skills !== []) {
            $query->where(function (Builder $skillQuery) use ($skills) {
                foreach ($skills as $skill) {
                    $skillQuery->orWhere('skills', 'like', '%'.$skill.'%');
                }
            });
        }

        $this->applyTalentPoolLocationFilter($query, $filters);

        $education = trim((string) ($filters['education'] ?? ''));
        if ($education !== '') {
            $query->where('education', 'like', '%'.$education.'%');
        }

        $this->applyTalentPoolExperienceFilter($query, $filters);

        $savedOnly = filter_var($filters['saved_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $shortlistedOnly = filter_var($filters['shortlisted_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($savedOnly || $shortlistedOnly) {
            $employerId = (int) ($filters['employer_user_id'] ?? 0);
            $query->whereIn('id', function ($sub) use ($employerId, $savedOnly, $shortlistedOnly) {
                $sub->select('candidate_ref_id')
                    ->from('employer_talent_pool_actions')
                    ->where('employer_user_id', $employerId)
                    ->where('candidate_source', self::SOURCE_TALENT_POOL)
                    ->when($savedOnly, fn ($q) => $q->where('is_saved', true))
                    ->when($shortlistedOnly, fn ($q) => $q->where('is_shortlisted', true));
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyVerifiedLocationFilter(Builder $query, array $filters): void
    {
        $locations = $this->selectedLocations($filters);
        if ($locations === []) {
            return;
        }

        $query->whereHas('candidateProfile', function (Builder $profile) use ($locations) {
            $profile->where(function (Builder $locQuery) use ($locations) {
                foreach ($locations as $location) {
                    $like = '%'.$location.'%';
                    $locQuery->orWhere('location', 'like', $like)
                        ->orWhere('preferred_job_location', 'like', $like);
                }
            });
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyTalentPoolLocationFilter(Builder $query, array $filters): void
    {
        $locations = $this->selectedLocations($filters);
        if ($locations === []) {
            return;
        }

        $query->where(function (Builder $locQuery) use ($locations) {
            foreach ($locations as $location) {
                $locQuery->orWhere('location', 'like', '%'.$location.'%');
            }
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyVerifiedExperienceFilter(Builder $query, array $filters): void
    {
        $expMin = $filters['experience_min'] ?? null;
        $expMax = $filters['experience_max'] ?? null;
        if (($expMin === null || $expMin === '') && ($expMax === null || $expMax === '')) {
            return;
        }

        $query->whereHas('candidateProfile', function (Builder $profile) use ($expMin, $expMax) {
            if ($expMin !== null && $expMin !== '') {
                $profile->where('experience_years', '>=', (int) $expMin);
            }
            if ($expMax !== null && $expMax !== '') {
                $profile->where('experience_years', '<=', (int) $expMax);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyTalentPoolExperienceFilter(Builder $query, array $filters): void
    {
        $expMin = $filters['experience_min'] ?? null;
        $expMax = $filters['experience_max'] ?? null;
        if ($expMin !== null && $expMin !== '') {
            $query->where('experience_years', '>=', (int) $expMin);
        }
        if ($expMax !== null && $expMax !== '') {
            $query->where('experience_years', '<=', (int) $expMax);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{label: string, count: int}>
     */
    protected function aggregateLocationFacets(array $filters): array
    {
        if (! $this->hasSearchCriteria($filters)) {
            return [];
        }

        $cityExpr = $this->citySqlExpression('location');
        $counts = [];
        $userSub = $this->verifiedCandidatesQuery($filters)->select('users.id');

        $locRows = DB::table('candidate_profiles')
            ->whereIn('user_id', $userSub)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->selectRaw("{$cityExpr} as label, COUNT(*) as aggregate")
            ->groupBy('label')
            ->having('label', '!=', '')
            ->orderByDesc('aggregate')
            ->limit(40)
            ->get();

        foreach ($locRows as $row) {
            $counts[$row->label] = (int) $row->aggregate;
        }

        $talentSub = $this->talentPoolCandidatesQuery($filters);
        $talentLocs = DB::query()
            ->fromSub($talentSub->selectRaw("{$cityExpr} as label"), 't')
            ->whereNotNull('label')
            ->where('label', '!=', '')
            ->selectRaw('label, COUNT(*) as aggregate')
            ->groupBy('label')
            ->orderByDesc('aggregate')
            ->limit(40)
            ->get();

        foreach ($talentLocs as $row) {
            $counts[$row->label] = ($counts[$row->label] ?? 0) + (int) $row->aggregate;
        }

        arsort($counts);

        return collect($counts)
            ->take(50)
            ->map(fn (int $count, string $label) => ['label' => $label, 'count' => $count])
            ->values()
            ->all();
    }

    protected function citySqlExpression(string $column): string
    {
        return "TRIM(SUBSTRING_INDEX(TRIM({$column}), ',', 1))";
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{label: string, count: int}>
     */
    protected function aggregateEducationFacets(array $filters): array
    {
        if (! $this->hasSearchCriteria($filters)) {
            return [];
        }

        $counts = [];
        $userSub = $this->verifiedCandidatesQuery($filters)->select('users.id');

        $eduRows = DB::table('candidate_profiles')
            ->whereIn('user_id', $userSub)
            ->whereNotNull('education')
            ->where('education', '!=', '')
            ->selectRaw('TRIM(education) as label, COUNT(*) as aggregate')
            ->groupBy('label')
            ->orderByDesc('aggregate')
            ->limit(15)
            ->get();

        foreach ($eduRows as $row) {
            $counts[$row->label] = (int) $row->aggregate;
        }

        $talentSub = $this->talentPoolCandidatesQuery($filters);
        $talentEdu = DB::query()
            ->fromSub($talentSub->selectRaw('TRIM(education) as label'), 't')
            ->whereNotNull('label')
            ->where('label', '!=', '')
            ->selectRaw('label, COUNT(*) as aggregate')
            ->groupBy('label')
            ->orderByDesc('aggregate')
            ->limit(15)
            ->get();

        foreach ($talentEdu as $row) {
            $counts[$row->label] = ($counts[$row->label] ?? 0) + (int) $row->aggregate;
        }

        arsort($counts);

        return collect($counts)
            ->take(20)
            ->map(fn (int $count, string $label) => ['label' => $label, 'count' => $count])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{label: string, min: int|null, max: int|null, count: int}>
     */
    protected function aggregateExperienceFacets(array $filters): array
    {
        if (! $this->hasSearchCriteria($filters)) {
            return [];
        }

        $buckets = [
            ['label' => 'Fresher (0 yr)', 'min' => 0, 'max' => 0],
            ['label' => '1 – 2 years', 'min' => 1, 'max' => 2],
            ['label' => '3 – 5 years', 'min' => 3, 'max' => 5],
            ['label' => '5 – 10 years', 'min' => 5, 'max' => 10],
            ['label' => '10+ years', 'min' => 10, 'max' => null],
        ];

        $result = [];
        foreach ($buckets as $bucket) {
            $bucketFilters = array_merge($filters, [
                'experience_min' => $bucket['min'],
                'experience_max' => $bucket['max'],
            ]);
            $slice = $this->fetchUnionPage($bucketFilters, 0, 1);
            if ($slice->isEmpty()) {
                continue;
            }
            $estimate = $this->fetchUnionPage($bucketFilters, 0, 5000)->count();
            if ($estimate > 0) {
                $result[] = [
                    'label' => $bucket['label'],
                    'min' => $bucket['min'],
                    'max' => $bucket['max'],
                    'count' => $estimate >= 5000 ? 5000 : $estimate,
                ];
            }
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    public function parseListFilterPublic(mixed $value): array
    {
        return $this->parseListFilter($value);
    }

    protected function parseListFilter(mixed $value): array
    {
        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = preg_split('/[,;]+/', (string) $value) ?: [];
        }

        return array_values(array_filter(array_map(fn ($s) => trim((string) $s), $parts)));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, array{is_saved: bool, is_shortlisted: bool}>
     */
    protected function actionMapForEmployer(int $employerUserId, Collection $items): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        $verifiedIds = $items->where('source', self::SOURCE_VERIFIED)->pluck('source_id')->all();
        $talentIds = $items->where('source', self::SOURCE_TALENT_POOL)->pluck('source_id')->all();

        $actions = EmployerTalentPoolAction::query()
            ->where('employer_user_id', $employerUserId)
            ->where(function (Builder $q) use ($verifiedIds, $talentIds) {
                if ($verifiedIds !== []) {
                    $q->orWhere(function (Builder $inner) use ($verifiedIds) {
                        $inner->where('candidate_source', self::SOURCE_VERIFIED)
                            ->whereIn('candidate_ref_id', $verifiedIds);
                    });
                }
                if ($talentIds !== []) {
                    $q->orWhere(function (Builder $inner) use ($talentIds) {
                        $inner->where('candidate_source', self::SOURCE_TALENT_POOL)
                            ->whereIn('candidate_ref_id', $talentIds);
                    });
                }
            })
            ->get();

        $map = [];
        foreach ($actions as $action) {
            $key = $action->candidate_source.':'.$action->candidate_ref_id;
            $map[$key] = [
                'is_saved' => $action->is_saved,
                'is_shortlisted' => $action->is_shortlisted,
            ];
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeVerified(User $user, bool $detailed = false): array
    {
        $profile = $user->candidateProfile;
        $resume = $user->relationLoaded('resumes')
            ? $user->resumes->first()
            : $user->resumes()->orderByDesc('is_primary')->orderByDesc('id')->first();

        $skills = $this->mergeSkills(
            $profile?->skills,
            $resume instanceof Resume ? $resume->extracted_skills : null
        );

        $row = [
            'key' => self::SOURCE_VERIFIED.':'.$user->id,
            'source' => self::SOURCE_VERIFIED,
            'source_id' => $user->id,
            'badge' => 'Verified Candidate',
            'badge_class' => 'verified',
            'full_name' => $user->name,
            'title' => $profile?->headline,
            'location' => $profile?->location ?: $profile?->preferred_job_location,
            'experience_years' => $profile?->experience_years,
            'experience_label' => $profile?->formattedTotalExperience(),
            'education' => $profile?->education,
            'skills' => $skills,
            'expected_salary' => $profile?->formattedExpectedSalary() ?? $profile?->expected_salary,
            'profile_summary' => $profile?->bio_summary ?: $profile?->career_objective,
            'profile_image' => $profile?->profilePhotoUrl(),
            'phone' => $user->phone,
            'email' => $user->email,
            'resume_url' => $resume ? route('resume.file', $resume) : null,
            'has_resume' => $resume !== null,
            'current_company' => $profile?->current_company,
            'preferred_location' => $profile?->preferred_job_location,
            'current_role' => $this->formatWorkLine($profile, 0),
            'previous_role' => $this->formatWorkLine($profile, 1),
            'is_saved' => false,
            'is_shortlisted' => false,
        ];

        if ($detailed) {
            $row['work_experience'] = CandidateProfile::jsonRepeaterToArray($profile?->work_experience);
            $row['work_experience_items'] = $this->formatWorkExperienceItems($profile?->work_experience);
            $row['education_history'] = CandidateProfile::jsonRepeaterToArray($profile?->education_history);
            $row['linkedin_url'] = $profile?->linkedin_url;
            $row['github_url'] = $profile?->github_url;
            $row['portfolio_url'] = $profile?->portfolio_url;
            $row['preferred_job_role'] = $profile?->preferred_job_role;
            $row['notice_period'] = $profile?->notice_period;
            $row['industries'] = array_slice($skills, 0, 10);
            $row['departments'] = array_values(array_filter([
                $profile?->preferred_job_role,
                $profile?->headline,
            ]));
            $row['active_label'] = $user->updated_at?->format("j M 'y") ?? null;
        }

        return $row;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeTalentPool(TalentPoolCandidate $candidate, bool $detailed = false): array
    {
        $row = [
            'key' => self::SOURCE_TALENT_POOL.':'.$candidate->id,
            'source' => self::SOURCE_TALENT_POOL,
            'source_id' => $candidate->id,
            'badge' => 'Talent Pool',
            'badge_class' => 'talent-pool',
            'full_name' => $candidate->full_name,
            'title' => $candidate->title,
            'location' => $candidate->location,
            'experience_years' => $candidate->experience_years,
            'experience_label' => $candidate->experience_years !== null
                ? $candidate->experience_years.' '.($candidate->experience_years === 1 ? 'year' : 'years')
                : null,
            'education' => $candidate->education,
            'skills' => $candidate->skillsList(),
            'expected_salary' => $candidate->expected_salary,
            'profile_summary' => $candidate->profile_summary,
            'profile_image' => filled($candidate->profile_image) ? $candidate->profile_image : null,
            'phone' => $candidate->phone,
            'email' => $candidate->email,
            'resume_url' => $candidate->resume_url,
            'has_resume' => filled($candidate->resume_url),
            'current_company' => null,
            'is_saved' => false,
            'is_shortlisted' => false,
        ];

        if ($detailed) {
            $workItems = [];
            if ($candidate->title || $candidate->location) {
                $workItems[] = [
                    'title' => $candidate->title ?? 'Role',
                    'company' => '',
                    'period' => null,
                    'is_current' => true,
                ];
            }
            $row['work_experience'] = [];
            $row['work_experience_items'] = $workItems;
            $row['education_history'] = [];
            $row['linkedin_url'] = null;
            $row['github_url'] = null;
            $row['portfolio_url'] = null;
            $row['preferred_job_role'] = $candidate->title;
            $row['notice_period'] = null;
            $row['industries'] = array_slice($candidate->skillsList(), 0, 10);
            $row['departments'] = array_values(array_filter([$candidate->title]));
            $row['active_label'] = $candidate->updated_at?->format("j M 'y") ?? null;
        }

        return $row;
    }

    /**
     * @return list<string>
     */
    protected function mergeSkills(mixed $profileSkills, mixed $resumeSkills): array
    {
        $list = [];
        if (is_string($profileSkills) && trim($profileSkills) !== '') {
            $list = array_merge($list, array_map('trim', explode(',', $profileSkills)));
        } elseif (is_array($profileSkills)) {
            $list = array_merge($list, $profileSkills);
        }
        if (is_array($resumeSkills)) {
            $list = array_merge($list, $resumeSkills);
        }

        return array_values(array_unique(array_filter(array_map(fn ($s) => is_string($s) ? trim($s) : '', $list))));
    }

    protected function formatWorkLine(?CandidateProfile $profile, int $index): ?string
    {
        if ($profile === null) {
            return null;
        }

        $rows = CandidateProfile::jsonRepeaterToArray($profile->work_experience);
        if (! isset($rows[$index]) || ! is_array($rows[$index])) {
            return null;
        }

        $row = $rows[$index];
        $title = trim((string) ($row['job_title'] ?? $row['title'] ?? $row['role'] ?? ''));
        $company = trim((string) ($row['company'] ?? $row['company_name'] ?? $row['organization'] ?? ''));

        if ($title !== '' && $company !== '') {
            return $title.' at '.$company;
        }

        return $title !== '' ? $title : ($company !== '' ? $company : null);
    }

    /**
     * @return list<array{title: string, company: string, period: string|null, is_current: bool}>
     */
    protected function formatWorkExperienceItems(mixed $workExperience): array
    {
        $rows = CandidateProfile::jsonRepeaterToArray($workExperience);
        $items = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $title = trim((string) ($row['job_title'] ?? $row['title'] ?? $row['role'] ?? ''));
            $company = trim((string) ($row['company'] ?? $row['company_name'] ?? $row['organization'] ?? ''));
            if ($title === '' && $company === '') {
                continue;
            }

            $start = trim((string) ($row['start_date'] ?? $row['from'] ?? $row['start_year'] ?? ''));
            $end = trim((string) ($row['end_date'] ?? $row['to'] ?? $row['end_year'] ?? ''));
            $isCurrent = $index === 0 || in_array(strtolower($end), ['', 'present', 'current', 'now'], true);
            $period = $this->formatExperiencePeriod($start, $end, $isCurrent);

            $items[] = [
                'title' => $title,
                'company' => $company,
                'period' => $period,
                'is_current' => $isCurrent,
            ];
        }

        return $items;
    }

    protected function formatExperiencePeriod(string $start, string $end, bool $isCurrent): ?string
    {
        if ($start === '' && $end === '' && ! $isCurrent) {
            return null;
        }

        if ($isCurrent) {
            return ($start !== '' ? $start.' – ' : '').'present';
        }

        if ($start !== '' && $end !== '') {
            return $start.' – '.$end;
        }

        return $start !== '' ? $start : ($end !== '' ? $end : null);
    }
}
