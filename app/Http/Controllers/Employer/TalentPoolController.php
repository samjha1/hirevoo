<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\CandidateProfile;
use App\Models\EmployerTalentPoolAction;
use App\Models\TalentPoolCandidate;
use App\Models\User;
use App\Services\EmployerPlanService;
use App\Services\TalentPoolSearchService;
use App\Services\TalentPoolTokenService;
use App\Support\TalentPoolDisplay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TalentPoolController extends Controller
{
    public function __construct(
        protected TalentPoolSearchService $searchService,
        protected EmployerPlanService $planService,
        protected TalentPoolTokenService $tokenService,
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
            'talentPoolTokens' => $this->planService->talentPoolTokens($user->referrerProfile),
            'viewTokenCost' => $this->tokenService->viewCost(),
            'downloadTokenCost' => $this->tokenService->downloadCost(),
            'filters' => $filters,
            'locationFacets' => $this->searchService->cityOptionsForFilters($filters),
            'preferredLocationFacets' => $this->searchService->preferredLocationOptionsForFilters($filters),
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
            withFacets: true,
            includeTotal: true,
        );

        $items = $result['items']->map(fn (array $row) => $this->planService->enrichCandidateRow($row, $user, true));
        $facetFilters = $this->searchService->filtersForFacetComputation($filters);
        $facets = TalentPoolDisplay::applyFacetCounts(
            $result['facets'] ?? $this->searchService->filterFacets($facetFilters)
        );

        return view('hirevo.employer.talent-pool.results', [
            'filters' => $filters,
            'selectedLocations' => $this->searchService->selectedLocations($filters),
            'facets' => $facets,
            'locationFacets' => $this->searchService->cityOptionsForFilters($filters),
            'preferredLocationFacets' => $this->searchService->preferredLocationOptionsForFilters($filters),
            'activeFilterCount' => $result['active_filter_count'],
            'educationOptions' => CandidateProfile::educationDegreeValues(),
            'candidates' => $items,
            'paginator' => $result['paginator'],
            'perPage' => $perPage,
            'totalCount' => isset($result['total_count']) ? TalentPoolDisplay::count((int) $result['total_count']) : null,
            'matchingSkills' => $this->matchingHighlightTerms($filters, $result['related_fallback'] ?? null),
            'requiresSearch' => (bool) ($result['requires_search'] ?? false),
            'canAccessTalentPool' => $canAccess,
            'talentPoolTokens' => $this->planService->talentPoolTokens($profile),
            'viewTokenCost' => $this->tokenService->viewCost(),
            'downloadTokenCost' => $this->tokenService->downloadCost(),
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
                'facets' => ['locations' => [], 'preferred_locations' => [], 'education' => [], 'experience' => [], 'salary' => []],
                'location_options' => $this->searchService->cityOptionsForFilters($filters),
                'preferred_location_options' => $this->searchService->preferredLocationOptionsForFilters($filters),
                'total_count' => 0,
            ]);
        }

        $countOnly = $request->boolean('count_only');
        $locationsOnly = $request->boolean('locations_only');

        if ($locationsOnly) {
            return response()->json([
                'location_options' => $this->searchService->cityOptionsForFilters($filters),
                'preferred_location_options' => $this->searchService->preferredLocationOptionsForFilters($filters),
            ]);
        }

        if ($countOnly) {
            $payload = $this->facetCountPayload($filters);
            $payload['location_options'] = $this->searchService->cityOptionsForFilters($filters);
            $payload['preferred_location_options'] = $this->searchService->preferredLocationOptionsForFilters($filters);

            return response()->json($payload);
        }

        $facetFilters = $this->searchService->filtersForFacetComputation($filters);
        $facets = TalentPoolDisplay::applyFacetCounts($this->searchService->filterFacets($facetFilters));
        $locationOptions = $this->searchService->cityOptionsForFilters($filters);
        $preferredLocationOptions = $this->searchService->preferredLocationOptionsForFilters($filters);

        return response()->json([
            'facets' => $facets,
            'location_options' => $locationOptions,
            'preferred_location_options' => $preferredLocationOptions,
            'filters_html' => view('hirevo.employer.talent-pool._filters', [
                'filters' => $filters,
                'selectedLocations' => $this->searchService->selectedLocations($filters),
                'facets' => $facets,
                'locationFacets' => $locationOptions,
                'preferredLocationFacets' => $preferredLocationOptions,
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

        $items = $result['items']->map(fn (array $row) => $this->planService->enrichCandidateRow($row, $user, true));

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
            $locationOptions = $this->searchService->cityOptionsForFilters($filters);
            $preferredLocationOptions = $this->searchService->preferredLocationOptionsForFilters($filters);
            $payload['filters_html'] = view('hirevo.employer.talent-pool._filters', [
                'filters' => $filters,
                'selectedLocations' => $this->searchService->selectedLocations($filters),
                'facets' => TalentPoolDisplay::applyFacetCounts($result['facets'] ?? ['locations' => [], 'preferred_locations' => [], 'education' => [], 'experience' => [], 'salary' => []]),
                'locationFacets' => $locationOptions,
                'preferredLocationFacets' => $preferredLocationOptions,
                'activeFilterCount' => $result['active_filter_count'],
                'educationOptions' => CandidateProfile::educationDegreeValues(),
            ])->render();
            $payload['location_options'] = $locationOptions;
            $payload['preferred_location_options'] = $preferredLocationOptions;
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

        $result = $this->tokenService->unlockProfileView(
            $user,
            $validated['source'],
            (int) $validated['source_id']
        );

        if (! ($result['ok'] ?? false)) {
            if (($result['error'] ?? '') === 'insufficient_tokens') {
                return response()->json([
                    'message' => 'insufficient_tokens',
                    'tokens_remaining' => $result['tokens_remaining'] ?? 0,
                    'tokens_required' => $result['tokens_required'] ?? $this->tokenService->viewCost(),
                ], 402);
            }

            return response()->json(['message' => $result['error'] ?? 'unlock_failed'], 422);
        }

        $details = $this->searchService->details($validated['source'], (int) $validated['source_id']);
        if ($details !== null) {
            $details = $this->planService->enrichCandidateRow($details, $user, false);
            $details['is_saved'] = (bool) ($result['is_saved'] ?? true);
        }

        return response()->json([
            'is_unlocked' => true,
            'is_saved' => (bool) ($result['is_saved'] ?? true),
            'candidate' => $details,
            'tokens_remaining' => $result['tokens_remaining'] ?? $this->planService->talentPoolTokens($user->referrerProfile),
            'tokens_spent' => $result['tokens_spent'] ?? 0,
        ]);
    }

    public function downloadList(Request $request): JsonResponse|StreamedResponse
    {
        $user = $this->requireApprovedEmployer();
        if ($user instanceof RedirectResponse) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $this->planService->canAccessTalentPool($user->referrerProfile)) {
            return response()->json(['message' => 'subscription_required'], 402);
        }

        $validated = $request->validate([
            'list' => 'required|string|in:saved,shortlisted',
        ]);

        $filters = $this->filtersFromRequest($request, $user->id);
        if ($validated['list'] === 'saved') {
            $filters['saved_only'] = true;
            $filters['shortlisted_only'] = false;
        } else {
            $filters['shortlisted_only'] = true;
            $filters['saved_only'] = false;
        }

        $candidates = $this->searchService->allMatchingCandidateRefs($user->id, $filters);

        if ($candidates === []) {
            return response()->json(['message' => 'No candidates in this list.'], 422);
        }

        $unlockResult = $this->tokenService->bulkUnlockDownloads($user, $candidates);
        if (! ($unlockResult['ok'] ?? false)) {
            if (($unlockResult['error'] ?? '') === 'insufficient_tokens') {
                return response()->json([
                    'message' => 'insufficient_tokens',
                    'tokens_remaining' => $unlockResult['tokens_remaining'] ?? 0,
                    'tokens_required' => $unlockResult['tokens_required'] ?? 0,
                ], 402);
            }

            return response()->json(['message' => $unlockResult['error'] ?? 'download_failed'], 422);
        }

        $rows = [];
        foreach ($candidates as $candidate) {
            $details = $this->searchService->details($candidate['source'], $candidate['source_id']);
            if ($details === null) {
                continue;
            }
            $details = $this->planService->enrichCandidateRow($details, $user, false);
            $rows[] = $this->candidateListExportRow($details);
        }

        if ($rows === []) {
            return response()->json(['message' => 'No candidate data available.'], 422);
        }

        $label = $validated['list'] === 'saved' ? 'saved' : 'shortlisted';
        $filename = 'talent-pool-'.$label.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($out, array_values($row));
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Tokens-Remaining' => (string) $this->planService->talentPoolTokens($user->referrerProfile),
            'X-Tokens-Spent' => (string) ($unlockResult['tokens_spent'] ?? 0),
        ]);
    }

    public function download(Request $request): JsonResponse|StreamedResponse
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

        if (! $this->tokenService->canDownload($user, $validated['source'], (int) $validated['source_id'])) {
            $result = $this->tokenService->unlockDownload(
                $user,
                $validated['source'],
                (int) $validated['source_id']
            );

            if (! ($result['ok'] ?? false)) {
                if (($result['error'] ?? '') === 'insufficient_tokens') {
                    return response()->json([
                        'message' => 'insufficient_tokens',
                        'tokens_remaining' => $result['tokens_remaining'] ?? 0,
                        'tokens_required' => $result['tokens_required'] ?? $this->tokenService->downloadCost(),
                    ], 402);
                }

                return response()->json(['message' => $result['error'] ?? 'download_failed'], 422);
            }
        }

        $details = $this->searchService->details($validated['source'], (int) $validated['source_id']);
        if ($details === null) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        $details = $this->planService->enrichCandidateRow($details, $user, false);
        $filename = 'candidate-'.$validated['source'].'-'.$validated['source_id'].'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($details) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Field', 'Value']);
            foreach ($this->candidateExportRows($details) as $label => $value) {
                fputcsv($out, [$label, $value]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Tokens-Remaining' => (string) $this->planService->talentPoolTokens($user->referrerProfile),
        ]);
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, string>
     */
    protected function candidateExportRows(array $details): array
    {
        $skills = is_array($details['skills'] ?? null)
            ? implode(', ', $details['skills'])
            : (string) ($details['skills'] ?? '');

        return array_filter([
            'Name' => (string) ($details['full_name'] ?? ''),
            'Phone' => (string) ($details['phone'] ?? ''),
            'Email' => (string) ($details['email'] ?? ''),
            'Title' => (string) ($details['title'] ?? ''),
            'Location' => (string) ($details['location'] ?? ''),
            'Experience' => (string) ($details['experience_label'] ?? ''),
            'Education' => (string) ($details['education'] ?? ''),
            'Skills' => $skills,
            'Expected salary' => (string) ($details['expected_salary'] ?? ''),
            'Summary' => (string) ($details['profile_summary'] ?? ''),
            'Source' => (string) ($details['badge'] ?? $details['source'] ?? ''),
        ], static fn (string $value): bool => $value !== '');
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, string>
     */
    protected function candidateListExportRow(array $details): array
    {
        $skills = is_array($details['skills'] ?? null)
            ? implode(', ', $details['skills'])
            : (string) ($details['skills'] ?? '');

        return [
            'Name' => (string) ($details['full_name'] ?? ''),
            'Phone' => (string) ($details['phone'] ?? ''),
            'Email' => (string) ($details['email'] ?? ''),
            'Title' => (string) ($details['title'] ?? ''),
            'Location' => (string) ($details['location'] ?? ''),
            'Preferred location' => (string) ($details['preferred_location'] ?? ''),
            'Experience' => (string) ($details['experience_label'] ?? ''),
            'Education' => (string) ($details['education'] ?? ''),
            'Skills' => $skills,
            'Expected salary' => (string) ($details['expected_salary'] ?? ''),
            'Summary' => (string) ($details['profile_summary'] ?? ''),
            'Source' => (string) ($details['badge'] ?? $details['source'] ?? ''),
        ];
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
        $canDownload = $this->planService->canDownloadCandidate($user, $source, $id);
        if (! $canViewContact) {
            $details = $this->planService->maskCandidateRow($details, $user, true);
        } else {
            $details = $this->planService->enrichCandidateRow($details, $user, false);
        }

        $action = EmployerTalentPoolAction::query()
            ->where('employer_user_id', $user->id)
            ->where('candidate_source', $source)
            ->where('candidate_ref_id', $id)
            ->first();

        $details['is_saved'] = (bool) ($action?->is_saved);
        $details['is_shortlisted'] = (bool) ($action?->is_shortlisted);
        $details['is_unlocked'] = $canViewContact;
        $details['can_download'] = $canDownload;

        return response()->json([
            'candidate' => $details,
            'can_view_contact' => $canViewContact,
            'can_download' => $canDownload,
            'tokens_remaining' => $this->planService->talentPoolTokens($user->referrerProfile),
            'view_token_cost' => $this->tokenService->viewCost(),
            'download_token_cost' => $this->tokenService->downloadCost(),
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

        return response()->json([
            'is_saved' => $action->is_saved,
            'message' => $action->is_saved ? 'Candidate saved.' : 'Candidate removed from saved.',
        ]);
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
            'preferred_location' => $request->input('preferred_location'),
            'preferred_locations' => $request->input('preferred_locations', []),
            'education' => $request->input('education'),
            'experience_min' => $request->input('experience_min'),
            'experience_max' => $request->input('experience_max'),
            'salary_min_lpa' => $request->input('salary_min_lpa'),
            'saved_only' => $request->boolean('saved_only'),
            'shortlisted_only' => $request->boolean('shortlisted_only'),
            'employer_user_id' => $employerUserId,
        ];

        return $this->searchService->normalizeListModeFilters($filters);
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
