<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\CandidateProfile;
use App\Models\EmployerTalentPoolAction;
use App\Models\TalentPoolCandidate;
use App\Models\User;
use App\Services\EmployerPlanService;
use App\Services\TalentPoolSearchService;
use App\Support\TalentPoolDisplay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TalentPoolController extends Controller
{
    public function __construct(
        protected TalentPoolSearchService $searchService,
        protected EmployerPlanService $planService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $filters = $this->filtersFromRequest($request, $user->id);

        return view('hirevo.employer.talent-pool.search', [
            'educationOptions' => CandidateProfile::educationDegreeValues(),
            'canAccessTalentPool' => $this->planService->canAccessTalentPool($user->referrerProfile),
            'filters' => $filters,
            'locationFacets' => [],
            'totalCount' => null,
        ]);
    }

    public function results(Request $request): View|RedirectResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $profile = $user->referrerProfile;
        $canAccess = $this->planService->canAccessTalentPool($profile);

        $filters = $this->filtersFromRequest($request, $user->id);
        $perPage = max(10, min(30, (int) $request->input('per_page', 20)));
        $result = $this->searchService->search(
            $user->id,
            $filters,
            $perPage,
            (int) $request->input('page', 1),
            withFacets: false,
            includeTotal: false,
        );

        $items = $result['items']->map(fn (array $row) => $this->planService->maskCandidateRow($row, $user, true));

        return view('hirevo.employer.talent-pool.results', [
            'filters' => $filters,
            'selectedLocations' => $this->searchService->selectedLocations($filters),
            'facets' => TalentPoolDisplay::applyFacetCounts(['locations' => [], 'education' => [], 'experience' => []]),
            'activeFilterCount' => $result['active_filter_count'],
            'educationOptions' => CandidateProfile::educationDegreeValues(),
            'candidates' => $items,
            'paginator' => $result['paginator'],
            'perPage' => $perPage,
            'totalCount' => isset($result['total_count']) ? TalentPoolDisplay::count((int) $result['total_count']) : null,
            'matchingSkills' => $this->matchingHighlightTerms($filters, $result['related_fallback'] ?? null),
            'requiresSearch' => (bool) ($result['requires_search'] ?? false),
            'canAccessTalentPool' => $canAccess,
            'currentPlan' => $this->planService->planKey($profile),
            'relatedFallback' => $result['related_fallback'] ?? null,
        ]);
    }

    public function facets(Request $request): JsonResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $this->filtersFromRequest($request, $user->id);

        if (! $this->searchService->hasSearchCriteria($filters)) {
            return response()->json([
                'facets' => ['locations' => [], 'education' => [], 'experience' => []],
                'total_count' => 0,
            ]);
        }

        $countOnly = $request->boolean('count_only');

        if ($countOnly) {
            return response()->json($this->facetCountPayload($filters));
        }

        $facetFilters = $this->searchService->filtersForFacetComputation($filters);
        $facets = TalentPoolDisplay::applyFacetCounts($this->searchService->filterFacets($facetFilters));

        return response()->json([
            'facets' => $facets,
            'filters_html' => view('hirevo.employer.talent-pool._filters', [
                'filters' => $filters,
                'selectedLocations' => $this->searchService->selectedLocations($filters),
                'facets' => $facets,
                'activeFilterCount' => $this->searchService->countActiveFilters($filters),
                'educationOptions' => CandidateProfile::educationDegreeValues(),
            ])->render(),
            ...$this->facetCountPayload($filters),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{total_count: int, exact_count?: int, related_fallback?: array<string, mixed>|null}
     */
    protected function facetCountPayload(array $filters): array
    {
        $counts = $this->searchService->countWithRelatedFallback($filters);

        $payload = [
            'total_count' => TalentPoolDisplay::count((int) $counts['total_count']),
            'exact_count' => TalentPoolDisplay::count((int) $counts['exact_count']),
        ];

        if (! empty($counts['related_fallback'])) {
            $payload['related_fallback'] = $counts['related_fallback'];
        }

        return $payload;
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $profile = $user->referrerProfile;
        $canAccess = $this->planService->canAccessTalentPool($profile);
        $filters = $this->filtersFromRequest($request, $user->id);
        $hasCriteria = $this->searchService->hasSearchCriteria($filters);
        $perPage = max(10, min(30, (int) $request->input('per_page', 20)));
        $withFacets = $request->boolean('facets');
        $includeTotal = ! $request->boolean('skip_total');
        $result = $this->searchService->search(
            $user->id,
            $filters,
            $perPage,
            (int) $request->input('page', 1),
            withFacets: $withFacets,
            includeTotal: $includeTotal,
        );

        $items = $result['items']->map(fn (array $row) => $this->planService->maskCandidateRow($row, $user, true));

        $html = view('hirevo.employer.talent-pool._results', [
            'candidates' => $items,
            'paginator' => $result['paginator'],
            'perPage' => $perPage,
            'totalCount' => TalentPoolDisplay::count((int) ($result['total_count'] ?? 0)),
            'matchingSkills' => $this->matchingHighlightTerms($filters, $result['related_fallback'] ?? null),
            'requiresSearch' => (bool) ($result['requires_search'] ?? false),
            'canAccessTalentPool' => $canAccess,
            'relatedFallback' => $result['related_fallback'] ?? null,
        ])->render();

        $payload = [
            'html' => $html,
            'active_filter_count' => $result['active_filter_count'],
            'total_count' => $includeTotal ? TalentPoolDisplay::count((int) ($result['total_count'] ?? 0)) : null,
            'requires_search' => (bool) ($result['requires_search'] ?? false),
            'related_fallback' => $result['related_fallback'] ?? null,
        ];

        if ($withFacets) {
            $payload['filters_html'] = view('hirevo.employer.talent-pool._filters', [
                'filters' => $filters,
                'selectedLocations' => $this->searchService->selectedLocations($filters),
                'facets' => TalentPoolDisplay::applyFacetCounts($result['facets'] ?? ['locations' => [], 'education' => [], 'experience' => []]),
                'activeFilterCount' => $result['active_filter_count'],
                'educationOptions' => CandidateProfile::educationDegreeValues(),
            ])->render();
        }

        return response()->json($payload);
    }

    public function unlock(Request $request): JsonResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $this->planService->canAccessTalentPool($user->referrerProfile)) {
            return response()->json([
                'message' => 'subscription_required',
                'redirect' => route('employer.plans.index'),
            ], 402);
        }

        $validated = $request->validate([
            'source' => 'required|string|in:verified,talent_pool',
            'source_id' => 'required|integer|min:1',
        ]);

        if (! $this->candidateExists($validated['source'], (int) $validated['source_id'])) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        $details = $this->searchService->details($validated['source'], (int) $validated['source_id']);

        return response()->json([
            'is_unlocked' => true,
            'candidate' => $details,
        ]);
    }

    public function details(Request $request, string $source, int $id): JsonResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! EmployerTalentPoolAction::validSource($source)) {
            return response()->json(['message' => 'Invalid candidate source.'], 422);
        }

        $details = $this->searchService->details($source, $id);
        if ($details === null) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        $canViewContact = $this->planService->canViewCandidate($user, $source, $id);
        if (! $canViewContact) {
            $details = $this->planService->maskCandidateRow($details, $user, true);
        }

        $action = EmployerTalentPoolAction::query()
            ->where('employer_user_id', $user->id)
            ->where('candidate_source', $source)
            ->where('candidate_ref_id', $id)
            ->first();

        $details['is_saved'] = (bool) ($action?->is_saved);
        $details['is_shortlisted'] = (bool) ($action?->is_shortlisted);
        $details['is_unlocked'] = $canViewContact;

        return response()->json([
            'candidate' => $details,
            'can_view_contact' => $canViewContact,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $this->planService->canAccessTalentPool($user->referrerProfile)) {
            return response()->json(['message' => 'subscription_required'], 402);
        }

        $validated = $request->validate([
            'source' => 'required|string|in:verified,talent_pool',
            'source_id' => 'required|integer|min:1',
        ]);

        if (! $this->candidateExists($validated['source'], (int) $validated['source_id'])) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        $action = EmployerTalentPoolAction::query()->firstOrNew([
            'employer_user_id' => $user->id,
            'candidate_source' => $validated['source'],
            'candidate_ref_id' => (int) $validated['source_id'],
        ]);

        $action->is_saved = ! $action->is_saved;
        $action->save();

        return response()->json(['is_saved' => $action->is_saved]);
    }

    public function shortlist(Request $request): JsonResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $this->planService->canAccessTalentPool($user->referrerProfile)) {
            return response()->json(['message' => 'subscription_required'], 402);
        }

        $validated = $request->validate([
            'source' => 'required|string|in:verified,talent_pool',
            'source_id' => 'required|integer|min:1',
        ]);

        if (! $this->candidateExists($validated['source'], (int) $validated['source_id'])) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        $action = EmployerTalentPoolAction::query()->firstOrNew([
            'employer_user_id' => $user->id,
            'candidate_source' => $validated['source'],
            'candidate_ref_id' => (int) $validated['source_id'],
        ]);

        $action->is_shortlisted = ! $action->is_shortlisted;
        $action->save();

        return response()->json(['is_shortlisted' => $action->is_shortlisted]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function filtersFromRequest(Request $request, int $employerUserId): array
    {
        return [
            'q' => $request->input('q'),
            'skills' => $request->input('skills'),
            'location' => $request->input('location'),
            'locations' => $request->input('locations', []),
            'education' => $request->input('education'),
            'experience_min' => $request->input('experience_min'),
            'experience_max' => $request->input('experience_max'),
            'saved_only' => $request->boolean('saved_only'),
            'shortlisted_only' => $request->boolean('shortlisted_only'),
            'employer_user_id' => $employerUserId,
        ];
    }

    protected function requireApprovedEmployer(): User|RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }

        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to use Talent Pool.');
        }

        return $user;
    }

    protected function candidateExists(string $source, int $sourceId): bool
    {
        if ($source === EmployerTalentPoolAction::SOURCE_VERIFIED) {
            return User::query()
                ->where('role', 'candidate')
                ->where('status', 'active')
                ->whereHas('candidateProfile')
                ->whereKey($sourceId)
                ->exists();
        }

        return TalentPoolCandidate::query()->discoverable()->whereKey($sourceId)->exists();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>|null  $relatedFallback
     * @return list<string>
     */
    protected function matchingHighlightTerms(array $filters, ?array $relatedFallback): array
    {
        $terms = $this->searchService->parseListFilterPublic($filters['skills'] ?? '');
        $queryTerms = preg_split('/[\s,;]+/', (string) ($filters['q'] ?? '')) ?: [];
        $terms = array_merge($terms, array_map('trim', $queryTerms));

        if (is_array($relatedFallback['keywords'] ?? null)) {
            $terms = array_merge($terms, $relatedFallback['keywords']);
        }

        return array_values(array_unique(array_filter(
            $terms,
            static fn (string $term): bool => strlen($term) >= 2
        )));
    }
}
