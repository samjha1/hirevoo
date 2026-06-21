<?php

namespace App\Services;

use App\Support\ElasticsearchClientFactory;
use App\Models\TalentPoolCandidate;
use App\Models\User;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

class TalentPoolElasticsearchService
{
    public const ENTITY_VERIFIED = 'verified';

    public const ENTITY_TALENT_POOL = 'talent_pool';

    private ?Client $client = null;

    private ?bool $clientAvailable = null;

    public function isEnabled(): bool
    {
        return (bool) config('elasticsearch.enabled', false)
            && config('elasticsearch.hosts') !== [];
    }

    public function canUseElasticsearch(): bool
    {
        return $this->isEnabled() && $this->client() !== null;
    }

    /**
     * Paginated search — primary path for 100k+ talent pool rows.
     *
     * @param  array<string, mixed>  $filters
     * @return array{hits: list<array{source: string, source_id: int, score: float}>, total: int}|null
     */
    public function searchPage(array $filters, int $from, int $size): ?array
    {
        if ($this->isEmployerActionFilterEmpty($filters)) {
            return ['hits' => [], 'total' => 0];
        }

        $client = $this->client();
        if ($client === null) {
            return null;
        }

        $maxWindow = (int) config('elasticsearch.talent_pool_max_result_window', 50000);
        $from = max(0, min($from, max(0, $maxWindow - 1)));
        $size = max(1, min($size, 100));

        try {
            $response = $client->search([
                'index' => $this->indexName(),
                'body' => [
                    'from' => $from,
                    'size' => $size,
                    'track_total_hits' => true,
                    '_source' => ['entity_type', 'entity_id'],
                    'query' => $this->buildFilterQuery($filters),
                    'sort' => [
                        ['_score' => ['order' => 'desc']],
                        ['entity_type' => ['order' => 'asc']],
                        ['entity_id' => ['order' => 'desc']],
                    ],
                ],
            ]);

            return [
                'hits' => $this->parseHits($response['hits']['hits'] ?? []),
                'total' => $this->parseTotal($response['hits']['total'] ?? 0),
            ];
        } catch (Throwable $e) {
            Log::warning('Talent pool Elasticsearch search failed.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function countMatching(array $filters): ?int
    {
        if ($this->isEmployerActionFilterEmpty($filters)) {
            return 0;
        }

        $client = $this->client();
        if ($client === null) {
            return null;
        }

        try {
            $response = $client->count([
                'index' => $this->indexName(),
                'body' => [
                    'query' => $this->buildFilterQuery($filters),
                ],
            ]);

            return (int) ($response['count'] ?? 0);
        } catch (Throwable $e) {
            Log::warning('Talent pool Elasticsearch count failed.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{locations: list<array{label: string, count: int}>, education: list<array{label: string, count: int}>, experience: list<array{label: string, min: int|null, max: int|null, count: int}>}|null
     */
    public function aggregateFacets(array $filters): ?array
    {
        if ($this->isEmployerActionFilterEmpty($filters)) {
            return ['locations' => [], 'education' => [], 'experience' => []];
        }

        $client = $this->client();
        if ($client === null) {
            return null;
        }

        $forLocations = $filters;
        unset($forLocations['location'], $forLocations['locations']);

        $forEducation = $filters;
        unset($forEducation['education']);

        $forExperience = $filters;
        unset($forExperience['experience_min'], $forExperience['experience_max']);

        try {
            return [
                'locations' => $this->termsFacet($forLocations, 'location_city', 50),
                'education' => $this->termsFacet($forEducation, 'education_raw', 20),
                'experience' => $this->experienceFacets($forExperience),
            ];
        } catch (Throwable $e) {
            Log::warning('Talent pool Elasticsearch facets failed.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function hasTextCriteria(array $filters): bool
    {
        $minLen = (int) config('hirevo_plans.min_search_length', 2);
        if (mb_strlen(trim((string) ($filters['q'] ?? ''))) >= $minLen) {
            return true;
        }

        return $this->parseSkills($filters['skills'] ?? '') !== [];
    }

    public function indexVerifiedUser(User $user): void
    {
        if (! $this->canUseElasticsearch() || $user->role !== 'candidate' || $user->status !== 'active') {
            $this->deleteDocument(self::ENTITY_VERIFIED, (int) $user->id);

            return;
        }

        $user->loadMissing(['candidateProfile', 'resumes']);
        $profile = $user->candidateProfile;
        if (! $profile) {
            $this->deleteDocument(self::ENTITY_VERIFIED, (int) $user->id);

            return;
        }

        $resume = $user->resumes->sortByDesc(fn ($r) => ($r->is_primary ? 1 : 0).$r->id)->first();
        $skills = $this->skillsText($profile->skills, $resume?->extracted_skills);
        $location = trim((string) ($profile->location ?? '').' '.(string) ($profile->preferred_job_location ?? ''));

        $this->upsertDocument(self::ENTITY_VERIFIED, (int) $user->id, [
            'entity_type' => self::ENTITY_VERIFIED,
            'entity_id' => (int) $user->id,
            'name' => (string) $user->name,
            'title' => (string) ($profile->headline ?? ''),
            'email' => (string) ($user->email ?? ''),
            'phone' => (string) ($user->phone ?? ''),
            'location' => $location,
            'location_city' => $this->extractCity($location),
            'education' => (string) ($profile->education ?? ''),
            'education_raw' => mb_substr(trim((string) ($profile->education ?? '')), 0, 256),
            'skills' => $skills,
            'profile_text' => $this->plainText(implode("\n", array_filter([
                $profile->bio_summary,
                $profile->career_objective,
                $profile->current_company,
            ]))),
            'experience_years' => (int) ($profile->experience_years ?? 0),
            'status' => 'active',
        ]);
    }

    public function indexTalentPoolCandidate(TalentPoolCandidate $candidate): void
    {
        if (! $this->canUseElasticsearch()) {
            return;
        }

        if ($candidate->status !== TalentPoolCandidate::STATUS_ACTIVE) {
            $this->deleteDocument(self::ENTITY_TALENT_POOL, (int) $candidate->id);

            return;
        }

        $location = (string) ($candidate->location ?? '');

        $this->upsertDocument(self::ENTITY_TALENT_POOL, (int) $candidate->id, [
            'entity_type' => self::ENTITY_TALENT_POOL,
            'entity_id' => (int) $candidate->id,
            'name' => (string) $candidate->full_name,
            'title' => (string) ($candidate->title ?? ''),
            'email' => (string) ($candidate->email ?? ''),
            'phone' => (string) ($candidate->phone ?? ''),
            'location' => $location,
            'location_city' => $this->extractCity($location),
            'education' => (string) ($candidate->education ?? ''),
            'education_raw' => mb_substr(trim((string) ($candidate->education ?? '')), 0, 256),
            'skills' => $this->skillsText($candidate->skills),
            'profile_text' => $this->plainText($candidate->profile_summary),
            'experience_years' => (int) ($candidate->experience_years ?? 0),
            'status' => 'active',
        ]);
    }

    public function deleteVerifiedUser(User $user): void
    {
        $this->deleteDocument(self::ENTITY_VERIFIED, (int) $user->id);
    }

    public function deleteTalentPoolCandidate(TalentPoolCandidate $candidate): void
    {
        $this->deleteDocument(self::ENTITY_TALENT_POOL, (int) $candidate->id);
    }

    public function ensureIndex(): void
    {
        $client = $this->client();
        if ($client === null) {
            $hosts = implode(', ', config('elasticsearch.hosts', ['http://127.0.0.1:9200']));

            throw new \RuntimeException(
                'Elasticsearch is not reachable at '.$hosts.'. '
                .'Start Elasticsearch/OpenSearch on that host, then run this command again. '
                .'For local dev without ES, set ELASTICSEARCH_ENABLED=false in .env (SQL search fallback).'
            );
        }

        $index = $this->indexName();
        if ($client->indices()->exists(['index' => $index])->asBool()) {
            return;
        }

        $maxWindow = (int) config('elasticsearch.talent_pool_max_result_window', 50000);

        $client->indices()->create([
            'index' => $index,
            'body' => [
                'settings' => [
                    'number_of_shards' => max(1, (int) config('elasticsearch.talent_pool_shards', 2)),
                    'number_of_replicas' => max(0, (int) config('elasticsearch.talent_pool_replicas', 0)),
                    'max_result_window' => $maxWindow,
                    'refresh_interval' => '5s',
                    'analysis' => [
                        'analyzer' => [
                            'talent_text' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'asciifolding'],
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'entity_type' => ['type' => 'keyword'],
                        'entity_id' => ['type' => 'integer'],
                        'status' => ['type' => 'keyword'],
                        'experience_years' => ['type' => 'integer'],
                        'location_city' => ['type' => 'keyword'],
                        'education_raw' => ['type' => 'keyword'],
                        'name' => ['type' => 'text', 'analyzer' => 'talent_text'],
                        'title' => ['type' => 'text', 'analyzer' => 'talent_text'],
                        'email' => ['type' => 'text', 'analyzer' => 'talent_text'],
                        'phone' => ['type' => 'text', 'analyzer' => 'talent_text'],
                        'location' => ['type' => 'text', 'analyzer' => 'talent_text'],
                        'education' => ['type' => 'text', 'analyzer' => 'talent_text'],
                        'skills' => ['type' => 'text', 'analyzer' => 'talent_text'],
                        'profile_text' => ['type' => 'text', 'analyzer' => 'talent_text'],
                    ],
                ],
            ],
        ]);
    }

    public function deleteIndexIfExists(): void
    {
        $client = $this->client();
        if ($client === null) {
            return;
        }

        $index = $this->indexName();
        try {
            if ($client->indices()->exists(['index' => $index])->asBool()) {
                $client->indices()->delete(['index' => $index]);
            }
        } catch (Throwable $e) {
            Log::warning('Could not delete talent pool Elasticsearch index.', [
                'index' => $index,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  callable(int): void|null  $onProgress
     * @return array{verified: int, talent_pool: int}
     */
    public function reindexAll(?callable $onProgress = null): array
    {
        $this->ensureIndex();

        $counts = ['verified' => 0, 'talent_pool' => 0];
        $chunk = max(100, (int) config('elasticsearch.talent_pool_reindex_chunk', 500));
        $bulkSize = max(100, (int) config('elasticsearch.talent_pool_bulk_size', 500));

        User::query()
            ->where('role', 'candidate')
            ->where('status', 'active')
            ->whereHas('candidateProfile')
            ->with(['candidateProfile', 'resumes'])
            ->orderBy('id')
            ->chunkById($chunk, function ($users) use (&$counts, $bulkSize, $onProgress) {
                $this->bulkIndexVerifiedUsers($users, $bulkSize);
                $counts['verified'] += $users->count();
                if ($onProgress !== null) {
                    $onProgress($users->count());
                }
            });

        TalentPoolCandidate::query()
            ->discoverable()
            ->orderBy('id')
            ->chunkById($chunk, function ($candidates) use (&$counts, $bulkSize, $onProgress) {
                $this->bulkIndexTalentPoolCandidates($candidates, $bulkSize);
                $counts['talent_pool'] += $candidates->count();
                if ($onProgress !== null) {
                    $onProgress($candidates->count());
                }
            });

        return $counts;
    }

    /**
     * @return list<string>
     */
    public function tokenize(string $q): array
    {
        $q = mb_strtolower(trim($q));
        if ($q === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_values(array_unique(array_filter(
            $parts,
            static fn (string $p): bool => mb_strlen($p) >= 2
        )));

        if ($tokens === [] && mb_strlen($q) >= 1) {
            return [$q];
        }

        return $tokens;
    }

    /**
     * @return list<string>
     */
    public function parseSkills(mixed $value): array
    {
        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = preg_split('/[,;]+/', (string) $value) ?: [];
        }

        return array_values(array_filter(array_map(
            static fn (string $s): string => mb_strtolower(trim($s)),
            $parts
        ), static fn (string $s): bool => mb_strlen($s) >= 2));
    }

    /**
     * @param  iterable<int, User>  $users
     */
    protected function bulkIndexVerifiedUsers(iterable $users, int $bulkSize): void
    {
        $client = $this->client();
        if ($client === null) {
            return;
        }

        $body = [];
        $index = $this->indexName();

        foreach ($users as $user) {
            if ($user->role !== 'candidate' || $user->status !== 'active' || ! $user->candidateProfile) {
                continue;
            }

            $doc = $this->verifiedDocumentBody($user);
            if ($doc === null) {
                continue;
            }

            $body[] = ['index' => ['_index' => $index, '_id' => $this->documentId(self::ENTITY_VERIFIED, (int) $user->id)]];
            $body[] = $doc;

            if (count($body) >= $bulkSize * 2) {
                $this->flushBulk($body);
            }
        }

        $this->flushBulk($body);
    }

    /**
     * @param  iterable<int, TalentPoolCandidate>  $candidates
     */
    protected function bulkIndexTalentPoolCandidates(iterable $candidates, int $bulkSize): void
    {
        $client = $this->client();
        if ($client === null) {
            return;
        }

        $body = [];
        $index = $this->indexName();

        foreach ($candidates as $candidate) {
            $doc = $this->talentPoolDocumentBody($candidate);
            if ($doc === null) {
                continue;
            }

            $body[] = ['index' => ['_index' => $index, '_id' => $this->documentId(self::ENTITY_TALENT_POOL, (int) $candidate->id)]];
            $body[] = $doc;

            if (count($body) >= $bulkSize * 2) {
                $this->flushBulk($body);
            }
        }

        $this->flushBulk($body);
    }

    /**
     * @param  list<array<string, mixed>>  $body
     */
    protected function flushBulk(array &$body): void
    {
        if ($body === []) {
            return;
        }

        $client = $this->client();
        if ($client === null) {
            $body = [];

            return;
        }

        try {
            $client->bulk(['body' => $body]);
        } catch (Throwable $e) {
            Log::warning('Talent pool bulk index batch failed.', [
                'message' => $e->getMessage(),
                'docs' => (int) (count($body) / 2),
            ]);
        }

        $body = [];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function verifiedDocumentBody(User $user): ?array
    {
        $profile = $user->candidateProfile;
        if (! $profile) {
            return null;
        }

        $resume = $user->resumes->sortByDesc(fn ($r) => ($r->is_primary ? 1 : 0).$r->id)->first();
        $skills = $this->skillsText($profile->skills, $resume?->extracted_skills);
        $location = trim((string) ($profile->location ?? '').' '.(string) ($profile->preferred_job_location ?? ''));
        $education = (string) ($profile->education ?? '');

        return [
            'entity_type' => self::ENTITY_VERIFIED,
            'entity_id' => (int) $user->id,
            'name' => (string) $user->name,
            'title' => (string) ($profile->headline ?? ''),
            'email' => (string) ($user->email ?? ''),
            'phone' => (string) ($user->phone ?? ''),
            'location' => $location,
            'location_city' => $this->extractCity($location),
            'education' => $education,
            'education_raw' => mb_substr(trim($education), 0, 256),
            'skills' => $skills,
            'profile_text' => $this->plainText(implode("\n", array_filter([
                $profile->bio_summary,
                $profile->career_objective,
                $profile->current_company,
            ]))),
            'experience_years' => (int) ($profile->experience_years ?? 0),
            'status' => 'active',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function talentPoolDocumentBody(TalentPoolCandidate $candidate): ?array
    {
        if ($candidate->status !== TalentPoolCandidate::STATUS_ACTIVE) {
            return null;
        }

        $location = (string) ($candidate->location ?? '');
        $education = (string) ($candidate->education ?? '');

        return [
            'entity_type' => self::ENTITY_TALENT_POOL,
            'entity_id' => (int) $candidate->id,
            'name' => (string) $candidate->full_name,
            'title' => (string) ($candidate->title ?? ''),
            'email' => (string) ($candidate->email ?? ''),
            'phone' => (string) ($candidate->phone ?? ''),
            'location' => $location,
            'location_city' => $this->extractCity($location),
            'education' => $education,
            'education_raw' => mb_substr(trim($education), 0, 256),
            'skills' => $this->skillsText($candidate->skills),
            'profile_text' => $this->plainText($candidate->profile_summary),
            'experience_years' => (int) ($candidate->experience_years ?? 0),
            'status' => 'active',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function buildFilterQuery(array $filters): array
    {
        $boolFilter = [
            ['term' => ['status' => 'active']],
            ...$this->buildStructuralFilters($filters),
            ...$this->buildEmployerDocFilters($filters),
        ];

        $relatedTerms = $this->parseRelatedTerms($filters);
        if ($relatedTerms !== []) {
            return [
                'bool' => [
                    'filter' => $boolFilter,
                    'must' => [$this->buildRelatedTermsQuery($relatedTerms)],
                ],
            ];
        }

        $mustClauses = $this->buildKeywordMustClauses($filters);
        if ($mustClauses === []) {
            return ['bool' => ['filter' => $boolFilter]];
        }

        return [
            'bool' => [
                'filter' => $boolFilter,
                'must' => $mustClauses,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    protected function buildKeywordMustClauses(array $filters): array
    {
        $must = [];

        $queryTerms = $this->parseQueryTerms(trim((string) ($filters['q'] ?? '')));
        if (count($queryTerms) > 1) {
            $must[] = [
                'bool' => [
                    'should' => array_map(fn (string $term) => $this->buildTextQuery($term), $queryTerms),
                    'minimum_should_match' => 1,
                ],
            ];
        } elseif (count($queryTerms) === 1) {
            $must[] = $this->buildTextQuery($queryTerms[0]);
        }

        $skills = $this->parseSkills($filters['skills'] ?? '');
        if ($skills !== []) {
            $must[] = [
                'bool' => [
                    'should' => array_map(fn (string $skill) => $this->buildTextQuery($skill), $skills),
                    'minimum_should_match' => 1,
                ],
            ];
        }

        return $must;
    }

    /**
     * Comma/semicolon-separated terms are OR keywords; a single term uses space-separated AND matching.
     *
     * @return list<string>
     */
    public function parseQueryTerms(string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }

        $segments = preg_split('/[,;]+/u', $q) ?: [];
        $segments = array_values(array_filter(array_map(
            static fn (string $segment): string => trim($segment),
            $segments
        ), static fn (string $segment): bool => $segment !== ''));

        return $segments !== [] ? $segments : [$q];
    }

    protected function buildTextQuery(string $searchText): array
    {
        $fields = [
            'name^4',
            'title^3',
            'skills^3',
            'profile_text^2',
            'education^2',
            'location',
            'email',
        ];

        $tokens = $this->tokenize($searchText);
        if (count($tokens) <= 1) {
            return [
                'multi_match' => [
                    'query' => $searchText,
                    'fields' => $fields,
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        return [
            'bool' => [
                'should' => [
                    [
                        'multi_match' => [
                            'query' => $searchText,
                            'fields' => $fields,
                            'type' => 'phrase',
                            'slop' => 2,
                        ],
                    ],
                    [
                        'multi_match' => [
                            'query' => $searchText,
                            'fields' => $fields,
                            'type' => 'cross_fields',
                            'operator' => 'and',
                        ],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * @param  list<string>  $terms
     */
    protected function buildRelatedTermsQuery(array $terms): array
    {
        $fields = [
            'name^4',
            'title^3',
            'skills^3',
            'profile_text^2',
            'education^2',
            'location',
            'email',
        ];

        $should = [];
        foreach ($terms as $term) {
            $term = trim($term);
            if ($term === '') {
                continue;
            }

            $should[] = [
                'multi_match' => [
                    'query' => $term,
                    'fields' => $fields,
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        return [
            'bool' => [
                'should' => $should,
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<string>
     */
    protected function parseRelatedTerms(array $filters): array
    {
        $terms = $filters['_related_terms'] ?? [];
        if (! is_array($terms)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($term) => trim((string) $term),
            $terms
        ), static fn (string $term): bool => $term !== ''));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    protected function buildStructuralFilters(array $filters): array
    {
        $clauses = [];

        $locations = $this->parseLocations($filters);
        if ($locations !== []) {
            $should = [];
            foreach ($locations as $location) {
                $city = $this->extractCity($location);
                if ($city !== '') {
                    $should[] = ['term' => ['location_city' => mb_strtolower($city)]];
                }
                $should[] = [
                    'wildcard' => [
                        'location' => [
                            'value' => '*'.mb_strtolower($location).'*',
                            'case_insensitive' => true,
                        ],
                    ],
                ];
            }
            $clauses[] = ['bool' => ['should' => $should, 'minimum_should_match' => 1]];
        }

        $education = trim((string) ($filters['education'] ?? ''));
        if ($education !== '') {
            $clauses[] = [
                'bool' => [
                    'should' => [
                        ['term' => ['education_raw' => mb_substr($education, 0, 256)]],
                        [
                            'wildcard' => [
                                'education' => [
                                    'value' => '*'.mb_strtolower($education).'*',
                                    'case_insensitive' => true,
                                ],
                            ],
                        ],
                    ],
                    'minimum_should_match' => 1,
                ],
            ];
        }

        $expMin = $filters['experience_min'] ?? null;
        $expMax = $filters['experience_max'] ?? null;
        if (($expMin !== null && $expMin !== '') || ($expMax !== null && $expMax !== '')) {
            $range = [];
            if ($expMin !== null && $expMin !== '') {
                $range['gte'] = (int) $expMin;
            }
            if ($expMax !== null && $expMax !== '') {
                $range['lte'] = (int) $expMax;
            }
            $clauses[] = ['range' => ['experience_years' => $range]];
        }

        return $clauses;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    protected function buildEmployerDocFilters(array $filters): array
    {
        $docIds = $filters['_employer_doc_ids'] ?? null;
        if ($docIds === null) {
            return [];
        }

        if ($docIds === []) {
            return [['match_none' => (object) []]];
        }

        return [['ids' => ['values' => array_values($docIds)]]];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function isEmployerActionFilterEmpty(array $filters): bool
    {
        $docIds = $filters['_employer_doc_ids'] ?? null;

        return is_array($docIds) && $docIds === [];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{label: string, count: int}>
     */
    protected function termsFacet(array $filters, string $field, int $size): array
    {
        $client = $this->client();
        if ($client === null) {
            return [];
        }

        $response = $client->search([
            'index' => $this->indexName(),
            'body' => [
                'size' => 0,
                'query' => $this->buildFilterQuery($filters),
                'aggs' => [
                    'facet' => [
                        'terms' => [
                            'field' => $field,
                            'size' => $size,
                            'min_doc_count' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $buckets = $response['aggregations']['facet']['buckets'] ?? [];
        $result = [];
        foreach ($buckets as $bucket) {
            $label = trim((string) ($bucket['key'] ?? ''));
            if ($label === '') {
                continue;
            }
            $result[] = [
                'label' => ucwords($label),
                'count' => (int) ($bucket['doc_count'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{label: string, min: int|null, max: int|null, count: int}>
     */
    protected function experienceFacets(array $filters): array
    {
        $client = $this->client();
        if ($client === null) {
            return [];
        }

        $buckets = [
            ['label' => 'Fresher (0 yr)', 'min' => 0, 'max' => 0],
            ['label' => '1 – 2 years', 'min' => 1, 'max' => 2],
            ['label' => '3 – 5 years', 'min' => 3, 'max' => 5],
            ['label' => '5 – 10 years', 'min' => 5, 'max' => 10],
            ['label' => '10+ years', 'min' => 10, 'max' => null],
        ];

        $aggs = [];
        foreach ($buckets as $i => $bucket) {
            $range = [];
            if ($bucket['min'] !== null) {
                $range['gte'] = $bucket['min'];
            }
            if ($bucket['max'] !== null) {
                $range['lte'] = $bucket['max'];
            }
            $aggs['exp_'.$i] = [
                'filter' => ['range' => ['experience_years' => $range]],
            ];
        }

        $response = $client->search([
            'index' => $this->indexName(),
            'body' => [
                'size' => 0,
                'query' => $this->buildFilterQuery($filters),
                'aggs' => $aggs,
            ],
        ]);

        $result = [];
        foreach ($buckets as $i => $bucket) {
            $count = (int) ($response['aggregations']['exp_'.$i]['doc_count'] ?? 0);
            if ($count > 0) {
                $result[] = [
                    'label' => $bucket['label'],
                    'min' => $bucket['min'],
                    'max' => $bucket['max'],
                    'count' => $count,
                ];
            }
        }

        return $result;
    }

    /**
     * @param  array<int, mixed>  $rawHits
     * @return list<array{source: string, source_id: int, score: float}>
     */
    protected function parseHits(array $rawHits): array
    {
        $hits = [];
        foreach ($rawHits as $hit) {
            $source = (string) ($hit['_source']['entity_type'] ?? '');
            $id = (int) ($hit['_source']['entity_id'] ?? 0);
            if ($id <= 0 || ! in_array($source, [self::ENTITY_VERIFIED, self::ENTITY_TALENT_POOL], true)) {
                continue;
            }
            $hits[] = [
                'source' => $source,
                'source_id' => $id,
                'score' => (float) ($hit['_score'] ?? 0),
            ];
        }

        return $hits;
    }

    protected function parseTotal(mixed $total): int
    {
        if (is_array($total)) {
            return (int) ($total['value'] ?? 0);
        }

        return (int) $total;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<string>
     */
    protected function parseLocations(array $filters): array
    {
        $locations = $filters['locations'] ?? [];
        if (is_string($locations) && $locations !== '') {
            $locations = [$locations];
        }
        if (! is_array($locations)) {
            $locations = [];
        }
        $locations = array_values(array_filter(array_map(
            static fn ($s) => trim((string) $s),
            $locations
        )));
        if ($locations === [] && trim((string) ($filters['location'] ?? '')) !== '') {
            $locations = [trim((string) $filters['location'])];
        }

        return $locations;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function buildSearchText(array $filters): string
    {
        $parts = array_filter([
            trim((string) ($filters['q'] ?? '')),
            implode(' ', $this->parseSkills($filters['skills'] ?? '')),
        ]);

        return trim(implode(' ', $parts));
    }

    protected function extractCity(string $location): string
    {
        $location = trim($location);
        if ($location === '') {
            return '';
        }

        $city = trim(explode(',', $location)[0] ?? $location);

        return mb_strtolower($city);
    }

    protected function client(): ?Client
    {
        if (! $this->isEnabled()) {
            return null;
        }

        if ($this->clientAvailable === false) {
            return null;
        }

        if ($this->client !== null) {
            return $this->client;
        }

        try {
            $this->client = ElasticsearchClientFactory::make();
            $this->client->ping();
            $this->clientAvailable = true;

            return $this->client;
        } catch (Throwable $e) {
            $this->clientAvailable = false;
            Log::warning('Elasticsearch unavailable for talent pool search.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function indexName(): string
    {
        return (string) config('elasticsearch.talent_pool_index', 'hirevo_talent_pool');
    }

    protected function documentId(string $entityType, int $entityId): string
    {
        return $entityType.'_'.$entityId;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    protected function upsertDocument(string $entityType, int $entityId, array $body): void
    {
        $client = $this->client();
        if ($client === null) {
            return;
        }

        try {
            $client->index([
                'index' => $this->indexName(),
                'id' => $this->documentId($entityType, $entityId),
                'body' => $body,
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to index talent pool document.', [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function deleteDocument(string $entityType, int $entityId): void
    {
        $client = $this->client();
        if ($client === null) {
            return;
        }

        try {
            $client->delete([
                'index' => $this->indexName(),
                'id' => $this->documentId($entityType, $entityId),
            ]);
        } catch (Throwable) {
            // Document may not exist.
        }
    }

    /**
     * @param  mixed  $profileSkills
     * @param  mixed  $resumeSkills
     */
    protected function skillsText($profileSkills, $resumeSkills = null): string
    {
        $parts = [];
        if (is_string($profileSkills) && trim($profileSkills) !== '') {
            $decoded = json_decode($profileSkills, true);
            if (is_array($decoded)) {
                $parts = array_merge($parts, $decoded);
            } else {
                $parts = array_merge($parts, preg_split('/[,;]+/', $profileSkills) ?: []);
            }
        }
        if (is_array($profileSkills)) {
            $parts = array_merge($parts, $profileSkills);
        }
        if (is_array($resumeSkills)) {
            $parts = array_merge($parts, $resumeSkills);
        } elseif (is_string($resumeSkills) && trim($resumeSkills) !== '') {
            $decoded = json_decode($resumeSkills, true);
            if (is_array($decoded)) {
                $parts = array_merge($parts, $decoded);
            }
        }

        return implode(' ', array_values(array_filter(array_map('trim', $parts))));
    }

    protected function plainText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
