<?php

namespace App\Services;

use App\Models\TalentPoolCandidate;
use App\Models\User;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
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

    /**
     * Ranked candidate refs after text search, or null to use SQL fallback.
     *
     * @param  array<string, mixed>  $filters
     * @return list<array{source: string, source_id: int, score: float}>|null
     */
    public function searchRanked(array $filters): ?array
    {
        if (! $this->hasTextCriteria($filters)) {
            return null;
        }

        if ($this->canUseElasticsearch()) {
            $hits = $this->searchElasticsearch($filters);
            if ($hits !== null) {
                return $hits;
            }
        }

        return $this->searchRankedSql($this->filtersWithoutEmployer($filters));
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

        $this->upsertDocument(self::ENTITY_VERIFIED, (int) $user->id, [
            'entity_type' => self::ENTITY_VERIFIED,
            'entity_id' => (int) $user->id,
            'name' => (string) $user->name,
            'title' => (string) ($profile->headline ?? ''),
            'email' => (string) ($user->email ?? ''),
            'phone' => (string) ($user->phone ?? ''),
            'location' => trim((string) ($profile->location ?? '').' '.(string) ($profile->preferred_job_location ?? '')),
            'education' => (string) ($profile->education ?? ''),
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

        $this->upsertDocument(self::ENTITY_TALENT_POOL, (int) $candidate->id, [
            'entity_type' => self::ENTITY_TALENT_POOL,
            'entity_id' => (int) $candidate->id,
            'name' => (string) $candidate->full_name,
            'title' => (string) ($candidate->title ?? ''),
            'email' => (string) ($candidate->email ?? ''),
            'phone' => (string) ($candidate->phone ?? ''),
            'location' => (string) ($candidate->location ?? ''),
            'education' => (string) ($candidate->education ?? ''),
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
            throw new \RuntimeException('Elasticsearch client is not available.');
        }

        $index = $this->indexName();
        if ($client->indices()->exists(['index' => $index])->asBool()) {
            return;
        }

        $client->indices()->create([
            'index' => $index,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
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

    /**
     * @return array{verified: int, talent_pool: int}
     */
    public function reindexAll(): array
    {
        $this->ensureIndex();

        $counts = ['verified' => 0, 'talent_pool' => 0];

        User::query()
            ->where('role', 'candidate')
            ->where('status', 'active')
            ->whereHas('candidateProfile')
            ->with(['candidateProfile', 'resumes'])
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$counts) {
                foreach ($users as $user) {
                    $this->indexVerifiedUser($user);
                    $counts['verified']++;
                }
            });

        TalentPoolCandidate::query()
            ->discoverable()
            ->orderBy('id')
            ->chunkById(100, function ($candidates) use (&$counts) {
                foreach ($candidates as $candidate) {
                    $this->indexTalentPoolCandidate($candidate);
                    $counts['talent_pool']++;
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
     * @param  array<string, mixed>  $filters
     * @return list<array{source: string, source_id: int, score: float}>|null
     */
    protected function searchElasticsearch(array $filters): ?array
    {
        $client = $this->client();
        if ($client === null) {
            return null;
        }

        $searchText = $this->buildSearchText($filters);
        if ($searchText === '') {
            return [];
        }

        try {
            $response = $client->search([
                'index' => $this->indexName(),
                'body' => [
                    'size' => (int) config('elasticsearch.talent_pool_search_limit', 250),
                    'track_total_hits' => false,
                    '_source' => ['entity_type', 'entity_id'],
                    'query' => $this->buildQuery($searchText, $filters),
                    'sort' => [
                        ['_score' => ['order' => 'desc']],
                        ['entity_id' => ['order' => 'desc']],
                    ],
                ],
            ]);

            $hits = [];
            foreach ($response['hits']['hits'] ?? [] as $hit) {
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
        } catch (Throwable $e) {
            Log::warning('Talent pool Elasticsearch search failed, using SQL fallback.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{source: string, source_id: int, score: float}>
     */
    protected function searchRankedSql(array $filters): array
    {
        $jobSearch = app(JobOpeningsSearchService::class);
        $q = trim((string) ($filters['q'] ?? ''));
        $skills = $this->parseSkills($filters['skills'] ?? '');
        $tokens = array_values(array_unique(array_merge(
            $jobSearch->tokenize($q),
            array_map('strtolower', $skills)
        )));

        $hits = [];
        $score = 1000.0;

        if ($q !== '') {
            $like = '%'.$q.'%';
            User::query()
                ->where('role', 'candidate')
                ->where('status', 'active')
                ->whereHas('candidateProfile')
                ->where(function ($outer) use ($like, $tokens) {
                    $outer->where('users.name', 'like', $like)
                        ->orWhere('users.email', 'like', $like)
                        ->orWhereHas('candidateProfile', function ($profile) use ($like, $tokens) {
                            $profile->where('headline', 'like', $like)
                                ->orWhere('location', 'like', $like)
                                ->orWhere('skills', 'like', $like)
                                ->orWhere('education', 'like', $like);
                            foreach ($tokens as $token) {
                                $tokenLike = '%'.$token.'%';
                                $profile->orWhere('headline', 'like', $tokenLike)
                                    ->orWhere('skills', 'like', $tokenLike);
                            }
                        });
                })
                ->orderByDesc('updated_at')
                ->limit(400)
                ->pluck('id')
                ->each(function ($id) use (&$hits, &$score) {
                    $hits[] = [
                        'source' => self::ENTITY_VERIFIED,
                        'source_id' => (int) $id,
                        'score' => $score,
                    ];
                    $score -= 0.1;
                });

            TalentPoolCandidate::query()
                ->discoverable()
                ->where(function ($outer) use ($like, $tokens) {
                    $outer->where('full_name', 'like', $like)
                        ->orWhere('title', 'like', $like)
                        ->orWhere('skills', 'like', $like);
                    foreach ($tokens as $token) {
                        $tokenLike = '%'.$token.'%';
                        $outer->orWhere('full_name', 'like', $tokenLike)
                            ->orWhere('skills', 'like', $tokenLike);
                    }
                })
                ->orderByDesc('created_at')
                ->limit(400)
                ->pluck('id')
                ->each(function ($id) use (&$hits, &$score) {
                    $hits[] = [
                        'source' => self::ENTITY_TALENT_POOL,
                        'source_id' => (int) $id,
                        'score' => $score,
                    ];
                    $score -= 0.1;
                });
        } elseif ($skills !== []) {
            foreach ($skills as $skill) {
                $skillLike = '%'.$skill.'%';
                User::query()
                    ->where('role', 'candidate')
                    ->where('status', 'active')
                    ->whereHas('candidateProfile', fn ($p) => $p->where('skills', 'like', $skillLike))
                    ->limit(200)
                    ->pluck('id')
                    ->each(function ($id) use (&$hits, &$score) {
                        $hits[] = ['source' => self::ENTITY_VERIFIED, 'source_id' => (int) $id, 'score' => $score];
                        $score -= 0.1;
                    });
            }
        }

        return $hits;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function buildQuery(string $searchText, array $filters): array
    {
        $fields = [
            'name^4',
            'title^3',
            'skills^3',
            'profile_text^2',
            'education^2',
            'location',
            'email',
            'phone',
        ];

        $boolFilter = [
            ['term' => ['status' => 'active']],
            ...$this->buildStructuralFilters($filters),
        ];

        return [
            'bool' => [
                'filter' => $boolFilter,
                'must' => [[
                    'multi_match' => [
                        'query' => $searchText,
                        'fields' => $fields,
                        'type' => 'best_fields',
                        'operator' => 'or',
                        'fuzziness' => 'AUTO',
                    ],
                ]],
            ],
        ];
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
                'wildcard' => [
                    'education' => [
                        'value' => '*'.mb_strtolower($education).'*',
                        'case_insensitive' => true,
                    ],
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
     * @return array<string, mixed>
     */
    protected function filtersWithoutEmployer(array $filters): array
    {
        $copy = $filters;
        unset($copy['employer_user_id']);

        return $copy;
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

    protected function canUseElasticsearch(): bool
    {
        return $this->isEnabled() && $this->client() !== null;
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
            $this->client = ClientBuilder::create()
                ->setHosts(config('elasticsearch.hosts'))
                ->build();
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
