<?php

namespace App\Services;

use App\Models\EmployerJob;
use App\Models\JobApplication;
use App\Models\JobRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class JobCatalogService
{
    private const OPENINGS_SESSION_KEY = 'job_openings_catalog_v2';

    public function __construct(
        protected JobOpeningsSearchService $jobSearch,
        protected EmployerPlanService $employerPlans,
    ) {}

    /**
     * Paginated job goals: real roles first (shuffled), then synthetic.
     */
    public function paginateJobGoals(Request $request, int $perPage = 24): LengthAwarePaginator
    {
        $base = $this->jobGoalsQuery($request);
        $page = max(1, (int) $request->get('page', 1));
        $seed = $this->dailySeed();

        $realIds = (clone $base)->real()->orderByRaw('RAND('.$seed.')')->pluck('id')->all();
        $syntheticCount = (clone $base)->synthetic()->count();
        $total = count($realIds) + $syntheticCount;
        $pageIds = $this->sliceCatalogIds($realIds, $base, 'synthetic', $page, $perPage);

        return $this->paginateIds(JobRole::class, $pageIds, $total, $perPage, $page, $request);
    }

    /**
     * Unified openings feed: employer jobs + job goals (real items first, shuffled once per filter).
     *
     * @return LengthAwarePaginator<int, array{type: string, model: EmployerJob|JobRole}>
     */
    public function paginateOpeningsCatalog(
        Request $request,
        Builder $employerQuery,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $page = max(1, (int) $request->get('page', 1));
        $goalBase = $this->jobGoalsQuery($request);
        $filterHash = $this->openingsFilterHash($request, $employerQuery);

        $cache = session(self::OPENINGS_SESSION_KEY);
        if (! is_array($cache) || ($cache['filter_hash'] ?? '') !== $filterHash) {
            $cache = $this->buildOpeningsCatalogCache(
                $employerQuery,
                $goalBase,
                $request->filled('q'),
            );
            $cache['filter_hash'] = $filterHash;
            $cache['last_synthetic_id'] = 0;
            $cache['last_loaded_page'] = 0;
        }

        $slice = $this->sliceOpeningsPage($cache, $goalBase, $page, $perPage);
        $cache['last_loaded_page'] = max((int) ($cache['last_loaded_page'] ?? 0), $page);
        session([self::OPENINGS_SESSION_KEY => $cache]);

        $total = count($cache['priority_entries']) + (int) $cache['synthetic_count'];

        return $this->hydrateOpeningsSlice($slice, $total, $perPage, $page, $request);
    }

    public function clearOpeningsCatalogCache(): void
    {
        session()->forget(self::OPENINGS_SESSION_KEY);
    }

    /**
     * @return array{employer_jobs: Collection<int, EmployerJob>, related_goals: Collection<int, JobRole>}
     */
    public function relatedForJobGoal(JobRole $jobRole, int $employerLimit = 15, int $goalLimit = 12): array
    {
        $keywords = array_values(array_unique(array_filter(
            array_map(
                'strtolower',
                array_merge(
                    array_filter(explode(' ', preg_replace('/[^a-z0-9\s]/i', ' ', $jobRole->title))),
                    $jobRole->requiredSkills->pluck('skill_name')->take(5)->map(fn ($s) => trim((string) $s))->all()
                )
            ),
            fn ($kw) => strlen($kw) >= 2
        )));

        $employerJobs = collect();
        if ($keywords !== []) {
            $query = EmployerJob::where('status', 'active')->with('user');
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('title', 'like', '%'.$kw.'%')
                        ->orWhere('description', 'like', '%'.$kw.'%');
                }
            });
            $employerJobs = $query->orderByDesc('created_at')->limit($employerLimit)->get();
        }

        $goalQuery = JobRole::active()
            ->where('id', '!=', $jobRole->id)
            ->withCount('requiredSkills');

        if ($jobRole->sector) {
            $goalQuery->where(function ($q) use ($jobRole, $keywords) {
                $q->where('sector', $jobRole->sector);
                if ($keywords !== []) {
                    foreach (array_slice($keywords, 0, 4) as $kw) {
                        $q->orWhere('title', 'like', '%'.$kw.'%');
                    }
                }
            });
        } elseif ($keywords !== []) {
            $goalQuery->where(function ($q) use ($keywords) {
                foreach (array_slice($keywords, 0, 4) as $kw) {
                    $q->orWhere('title', 'like', '%'.$kw.'%');
                }
            });
        }

        $seed = $this->dailySeed() + (int) $jobRole->id;
        $relatedGoals = $goalQuery
            ->orderBy('is_synthetic')
            ->orderByRaw('RAND('.$seed.')')
            ->limit($goalLimit)
            ->get();

        return [
            'employer_jobs' => $employerJobs,
            'related_goals' => $relatedGoals,
        ];
    }

    public function homeFeaturedJobGoals(int $limit = 11): Collection
    {
        $seed = $this->dailySeed();

        return JobRole::active()
            ->real()
            ->withCount('requiredSkills')
            ->orderByRaw('RAND('.$seed.')')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{filter_hash?: string, priority_entries: list<array{type: string, id: int}>, synthetic_count: int, last_synthetic_id?: int, last_loaded_page?: int}
     */
    protected function buildOpeningsCatalogCache(
        Builder $employerQuery,
        Builder $goalBase,
        bool $preserveSearchOrder = false,
    ): array {
        $seed = $this->dailySeed();

        $employerIdsQuery = clone $employerQuery;
        if (! $preserveSearchOrder) {
            $employerIdsQuery->orderByDesc('created_at');
        }
        $employerIds = $this->prioritizeEmployerJobIds(
            $employerIdsQuery->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $preserveSearchOrder,
        );

        $realGoalIds = (clone $goalBase)->real()->orderByRaw('RAND('.($seed + 1).')')->pluck('id')->all();

        $priorityEntries = [];
        foreach ($employerIds as $id) {
            $priorityEntries[] = ['type' => 'employer', 'id' => (int) $id];
        }
        $goalEntries = [];
        foreach ($realGoalIds as $id) {
            $goalEntries[] = ['type' => 'goal', 'id' => (int) $id];
        }
        shuffle($goalEntries);
        $priorityEntries = array_merge($priorityEntries, $goalEntries);

        $filterKey = $this->builderFingerprint($goalBase);

        return [
            'priority_entries' => $priorityEntries,
            'synthetic_count' => Cache::remember(
                'hirevo.synthetic_goals_count.'.$filterKey,
                now()->addMinutes(10),
                fn () => (clone $goalBase)->synthetic()->count()
            ),
            'last_synthetic_id' => 0,
            'last_loaded_page' => 0,
        ];
    }

    /**
     * Paid-plan employer jobs first; optional stable merge keeps search relevance within each tier.
     *
     * @param  list<int>  $ids
     * @return list<int>
     */
    protected function prioritizeEmployerJobIds(array $ids, bool $preserveRelativeOrder): array
    {
        if ($ids === []) {
            return [];
        }

        $jobs = EmployerJob::query()
            ->whereIn('id', $ids)
            ->with('user.referrerProfile')
            ->get()
            ->keyBy('id');

        $positions = array_flip($ids);
        $subscribed = [];
        $regular = [];

        foreach ($ids as $id) {
            $job = $jobs->get($id);
            if ($job === null) {
                continue;
            }

            $profile = $job->user?->referrerProfile;
            if ($this->employerPlans->hasActiveSubscription($profile)) {
                $subscribed[] = $id;
            } else {
                $regular[] = $id;
            }
        }

        if ($subscribed !== []) {
            usort($subscribed, function (int $a, int $b) use ($jobs, $positions, $preserveRelativeOrder): int {
                $profileA = $jobs->get($a)?->user?->referrerProfile;
                $profileB = $jobs->get($b)?->user?->referrerProfile;
                $rankA = $this->employerPlans->planPriceRank($this->employerPlans->planKey($profileA));
                $rankB = $this->employerPlans->planPriceRank($this->employerPlans->planKey($profileB));

                if ($rankA !== $rankB) {
                    return $rankB <=> $rankA;
                }

                if ($preserveRelativeOrder) {
                    return ($positions[$a] ?? 0) <=> ($positions[$b] ?? 0);
                }

                return $b <=> $a;
            });
        }

        $ordered = array_merge($subscribed, $regular);
        $catalogEmails = config('hirevo.catalog_employer_emails', []);
        $native = [];
        $catalog = [];

        foreach ($ordered as $id) {
            $job = $jobs->get($id);
            if ($job !== null && $job->isCatalogListing()) {
                $catalog[] = $id;
            } else {
                $native[] = $id;
            }
        }

        return array_merge($native, $catalog);
    }

    /**
     * @param  array{priority_entries: list<array{type: string, id: int}>, synthetic_count: int, last_synthetic_id?: int, last_loaded_page?: int}  $cache
     * @return list<array{type: string, id: int}>
     */
    protected function sliceOpeningsPage(array &$cache, Builder $goalBase, int $page, int $perPage): array
    {
        $priority = $cache['priority_entries'];
        $priorityCount = count($priority);
        $offset = ($page - 1) * $perPage;
        $lastLoaded = (int) ($cache['last_loaded_page'] ?? 0);
        $sequential = $page === 1 || $page === $lastLoaded + 1;

        if ($offset < $priorityCount) {
            $slice = array_slice($priority, $offset, $perPage);
            $remaining = $perPage - count($slice);
            if ($remaining > 0) {
                $afterId = (int) ($cache['last_synthetic_id'] ?? 0);
                $syn = $this->fetchSyntheticEntries($goalBase, $afterId, $remaining);
                $slice = array_merge($slice, $syn['entries']);
                $cache['last_synthetic_id'] = $syn['last_id'];
            }

            return $slice;
        }

        $synOffset = $offset - $priorityCount;

        if ($sequential) {
            $afterId = (int) ($cache['last_synthetic_id'] ?? 0);
            $syn = $this->fetchSyntheticEntries($goalBase, $afterId, $perPage);

            $cache['last_synthetic_id'] = $syn['last_id'];

            return $syn['entries'];
        }

        $anchor = $this->syntheticAnchorAtOffset($goalBase, $synOffset, $cache);
        $syn = $this->fetchSyntheticEntries($goalBase, $anchor, $perPage);
        $cache['last_synthetic_id'] = $syn['last_id'];

        return $syn['entries'];
    }

    /**
     * @return array{entries: list<array{type: string, id: int}>, last_id: int}
     */
    protected function fetchSyntheticEntries(Builder $goalBase, int $afterId, int $limit): array
    {
        $ids = (clone $goalBase)
            ->synthetic()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id')
            ->all();

        $entries = [];
        $lastId = $afterId;
        foreach ($ids as $id) {
            $entries[] = ['type' => 'goal', 'id' => (int) $id];
            $lastId = (int) $id;
        }

        return ['entries' => $entries, 'last_id' => $lastId];
    }

    protected function syntheticAnchorAtOffset(Builder $goalBase, int $synOffset, array $cache): int
    {
        if ($synOffset <= 0) {
            return 0;
        }

        $filterKey = $this->builderFingerprint($goalBase);

        return (int) Cache::remember(
            'hirevo.synthetic_anchor.'.$filterKey.'.'.$synOffset,
            now()->addHour(),
            function () use ($goalBase, $synOffset) {
                $id = (clone $goalBase)
                    ->synthetic()
                    ->orderBy('id')
                    ->offset(max(0, $synOffset - 1))
                    ->value('id');

                return $id ? (int) $id : 0;
            }
        );
    }

    /**
     * @param  list<array{type: string, id: int}>  $slice
     * @return LengthAwarePaginator<int, array{type: string, model: EmployerJob|JobRole}>
     */
    protected function hydrateOpeningsSlice(
        array $slice,
        int $total,
        int $perPage,
        int $page,
        Request $request,
    ): LengthAwarePaginator {
        $employerIdList = [];
        $goalIdList = [];
        foreach ($slice as $entry) {
            if ($entry['type'] === 'employer') {
                $employerIdList[] = $entry['id'];
            } else {
                $goalIdList[] = $entry['id'];
            }
        }

        $employers = $employerIdList === []
            ? collect()
            : EmployerJob::whereIn('id', $employerIdList)->with(['user.referrerProfile'])->get()->keyBy('id');
        $goals = $goalIdList === []
            ? collect()
            : JobRole::whereIn('id', $goalIdList)->get()->keyBy('id');

        $items = [];
        foreach ($slice as $entry) {
            if ($entry['type'] === 'employer' && $employers->has($entry['id'])) {
                $items[] = ['type' => 'employer', 'model' => $employers->get($entry['id'])];
            } elseif ($entry['type'] === 'goal' && $goals->has($entry['id'])) {
                $items[] = ['type' => 'goal', 'model' => $goals->get($entry['id'])];
            }
        }

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );
        $paginator->withQueryString();

        return $paginator;
    }

    protected function openingsFilterHash(Request $request, Builder $employerQuery): string
    {
        return md5(json_encode([
            'q' => (string) $request->get('q', ''),
            'location' => (string) $request->get('location', ''),
            'country' => (string) $request->get('country', ''),
            'job_type' => (string) $request->get('job_type', ''),
            'work_location_type' => (string) $request->get('work_location_type', ''),
            'user' => auth()->id() ?? 0,
            'employer' => $this->builderFingerprint($employerQuery),
            'goals' => $this->builderFingerprint($this->jobGoalsQuery($request)),
        ]));
    }

    protected function builderFingerprint(Builder $query): string
    {
        return md5($query->toSql().'|'.json_encode($query->getBindings()));
    }

    protected function jobGoalsQuery(Request $request): Builder
    {
        $query = JobRole::active();

        if ($request->filled('q')) {
            $this->jobSearch->applyJobRoleSearch($query, (string) $request->get('q'));
        }

        if (auth()->check()) {
            $appliedGoalIds = JobApplication::query()
                ->where('user_id', auth()->id())
                ->pluck('job_role_id')
                ->all();
            if ($appliedGoalIds !== []) {
                $query->whereNotIn('id', $appliedGoalIds);
            }
        }

        return $query;
    }

    /**
     * @param  list<int>  $priorityIds
     * @return list<int>
     */
    protected function sliceCatalogIds(
        array $priorityIds,
        Builder $syntheticBase,
        string $syntheticScope,
        int $page,
        int $perPage,
    ): array {
        $offset = ($page - 1) * $perPage;
        $priorityCount = count($priorityIds);

        if ($offset < $priorityCount) {
            $ids = array_slice($priorityIds, $offset, $perPage);
            $remaining = $perPage - count($ids);
            if ($remaining > 0) {
                $ids = array_merge(
                    $ids,
                    (clone $syntheticBase)->{$syntheticScope}()->orderBy('id')->limit($remaining)->pluck('id')->all()
                );
            }

            return $ids;
        }

        $synOffset = $offset - $priorityCount;

        return (clone $syntheticBase)->{$syntheticScope}()->orderBy('id')->offset($synOffset)->limit($perPage)->pluck('id')->all();
    }

    /**
     * @param  class-string<JobRole>  $modelClass
     * @param  list<int>  $pageIds
     */
    protected function paginateIds(
        string $modelClass,
        array $pageIds,
        int $total,
        int $perPage,
        int $page,
        Request $request,
    ): LengthAwarePaginator {
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($lastPage, $page);

        $pageItems = [];
        if ($pageIds !== []) {
            $map = $modelClass::whereIn('id', $pageIds)->withCount('requiredSkills')->get()->keyBy('id');
            foreach ($pageIds as $id) {
                if ($map->has($id)) {
                    $pageItems[] = $map->get($id);
                }
            }
        }

        $paginator = new LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );
        $paginator->withQueryString();

        return $paginator;
    }

    protected function dailySeed(): int
    {
        return (int) date('Ymd');
    }
}
