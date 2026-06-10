<?php

namespace App\Services;

use App\Models\EmployerJob;
use App\Models\JobRole;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobOpeningsSearchService
{
    private ?Client $client = null;

    private ?bool $clientAvailable = null;

    public function isEnabled(): bool
    {
        return (bool) config('elasticsearch.enabled', false)
            && config('elasticsearch.hosts') !== [];
    }

    public function applyEmployerJobSearch(Builder $query, string $rawQuery): void
    {
        $rawQuery = $this->normalizeQuery($rawQuery);
        if ($rawQuery === '') {
            return;
        }

        $ids = $this->matchingEmployerJobIds($rawQuery);
        if ($ids === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $table = $query->getModel()->getTable();
        $query->whereIn($table.'.id', $ids);
        $this->orderByFieldIds($query, $table.'.id', $ids);
    }

    public function applyJobRoleSearch(Builder $query, string $rawQuery): void
    {
        $rawQuery = $this->normalizeQuery($rawQuery);
        if ($rawQuery === '') {
            return;
        }

        $ids = $this->matchingJobRoleIds($rawQuery);
        if ($ids === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $table = $query->getModel()->getTable();
        $query->whereIn($table.'.id', $ids);
        $this->orderByFieldIds($query, $table.'.id', $ids);
    }

    /**
     * @return list<int>
     */
    public function matchingEmployerJobIds(string $rawQuery): array
    {
        $rawQuery = trim($rawQuery);
        if ($rawQuery === '') {
            return [];
        }

        if ($this->canUseElasticsearch()) {
            $ids = $this->searchElasticsearch($rawQuery, 'employer_job');
            if ($ids !== null) {
                return $ids;
            }
        }

        return $this->searchEmployerJobsSql($rawQuery);
    }

    /**
     * @return list<int>
     */
    public function matchingJobRoleIds(string $rawQuery): array
    {
        $rawQuery = trim($rawQuery);
        if ($rawQuery === '') {
            return [];
        }

        if ($this->canUseElasticsearch()) {
            $ids = $this->searchElasticsearch($rawQuery, 'job_role');
            if ($ids !== null) {
                return $ids;
            }
        }

        return $this->searchJobRolesSql($rawQuery);
    }

    public function indexEmployerJob(EmployerJob $job): void
    {
        if (! $this->canUseElasticsearch()) {
            return;
        }

        if ($job->status !== 'active') {
            $this->deleteDocument('employer_job', (int) $job->id);

            return;
        }

        $job->loadMissing('user.referrerProfile');

        $this->upsertDocument('employer_job', (int) $job->id, [
            'entity_type' => 'employer_job',
            'entity_id' => (int) $job->id,
            'title' => (string) $job->title,
            'description' => $this->plainText($job->description),
            'company_name' => $this->employerCompanyName($job),
            'sector' => (string) ($job->job_department ?? ''),
            'location' => (string) ($job->formatted_location ?? $job->location ?? ''),
            'skills' => $this->skillsText($job->required_skills),
            'status' => (string) $job->status,
        ]);
    }

    public function indexJobRole(JobRole $role): void
    {
        if (! $this->canUseElasticsearch()) {
            return;
        }

        if (! $role->is_active) {
            $this->deleteDocument('job_role', (int) $role->id);

            return;
        }

        $this->upsertDocument('job_role', (int) $role->id, [
            'entity_type' => 'job_role',
            'entity_id' => (int) $role->id,
            'title' => (string) $role->title,
            'description' => $this->plainText($role->description),
            'company_name' => '',
            'sector' => (string) ($role->sector ?? ''),
            'location' => '',
            'skills' => '',
            'status' => 'active',
        ]);
    }

    public function deleteEmployerJob(EmployerJob $job): void
    {
        $this->deleteDocument('employer_job', (int) $job->id);
    }

    public function deleteJobRole(JobRole $role): void
    {
        $this->deleteDocument('job_role', (int) $role->id);
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
                            'job_text' => [
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
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'job_text',
                            'fields' => ['keyword' => ['type' => 'keyword']],
                        ],
                        'description' => ['type' => 'text', 'analyzer' => 'job_text'],
                        'company_name' => ['type' => 'text', 'analyzer' => 'job_text'],
                        'sector' => ['type' => 'text', 'analyzer' => 'job_text'],
                        'location' => ['type' => 'text', 'analyzer' => 'job_text'],
                        'skills' => ['type' => 'text', 'analyzer' => 'job_text'],
                    ],
                ],
            ],
        ]);
    }

    public function reindexAll(): array
    {
        $this->ensureIndex();

        $counts = ['employer_jobs' => 0, 'job_roles' => 0];

        EmployerJob::query()
            ->where('status', 'active')
            ->with('user.referrerProfile')
            ->orderBy('id')
            ->chunkById(100, function ($jobs) use (&$counts) {
                foreach ($jobs as $job) {
                    $this->indexEmployerJob($job);
                    $counts['employer_jobs']++;
                }
            });

        JobRole::query()
            ->active()
            ->orderBy('id')
            ->chunkById(100, function ($roles) use (&$counts) {
                foreach ($roles as $role) {
                    $this->indexJobRole($role);
                    $counts['job_roles']++;
                }
            });

        return $counts;
    }

    /**
     * @return list<string>
     */
    public function normalizeQuery(string $rawQuery): string
    {
        $rawQuery = html_entity_decode(trim($rawQuery), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $rawQuery = preg_replace('/[&\/,+|;]+/u', ' ', $rawQuery) ?? $rawQuery;
        $rawQuery = preg_replace('/\s+/u', ' ', $rawQuery) ?? $rawQuery;

        return trim($rawQuery);
    }

    public function tokenize(string $q): array
    {
        $q = mb_strtolower($this->normalizeQuery($q));
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
            Log::warning('Elasticsearch unavailable, using SQL job search fallback.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function indexName(): string
    {
        return (string) config('elasticsearch.index', 'hirevo_job_openings');
    }

    /**
     * @return list<int>|null Null when ES failed mid-request.
     */
    protected function searchElasticsearch(string $rawQuery, string $entityType): ?array
    {
        $client = $this->client();
        if ($client === null) {
            return null;
        }

        $tokens = $this->tokenize($rawQuery);
        if ($tokens === []) {
            return [];
        }

        try {
            $response = $client->search([
                'index' => $this->indexName(),
                'body' => [
                    'size' => (int) config('elasticsearch.search_limit', 500),
                    'track_total_hits' => false,
                    'query' => $this->buildElasticsearchQuery($tokens, $rawQuery, $entityType),
                    'sort' => [
                        ['_score' => ['order' => 'desc']],
                        ['entity_id' => ['order' => 'desc']],
                    ],
                ],
            ]);

            $hits = $response['hits']['hits'] ?? [];
            $ids = [];
            foreach ($hits as $hit) {
                $id = (int) ($hit['_source']['entity_id'] ?? 0);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }

            return $ids;
        } catch (Throwable $e) {
            Log::warning('Elasticsearch job search failed, using SQL fallback.', [
                'message' => $e->getMessage(),
                'entity_type' => $entityType,
            ]);

            return null;
        }
    }

    /**
     * @param  list<string>  $tokens
     * @return array<string, mixed>
     */
    protected function buildElasticsearchQuery(array $tokens, string $rawQuery, string $entityType): array
    {
        $fields = [
            'title^4',
            'company_name^3',
            'sector^2',
            'skills^2',
            'description',
            'location',
        ];

        $should = [];

        $phrase = trim($rawQuery);
        if ($phrase !== '') {
            $should[] = [
                'multi_match' => [
                    'query' => $phrase,
                    'fields' => $fields,
                    'type' => 'best_fields',
                    'operator' => 'or',
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        foreach ($tokens as $token) {
            $should[] = [
                'multi_match' => [
                    'query' => $token,
                    'fields' => $fields,
                    'fuzziness' => 'AUTO',
                    'operator' => 'or',
                ],
            ];
        }

        return [
            'bool' => [
                'filter' => [
                    ['term' => ['entity_type' => $entityType]],
                ],
                'should' => $should,
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * @return list<int>
     */
    protected function searchEmployerJobsSql(string $rawQuery): array
    {
        $tokens = $this->tokenize($rawQuery);
        $query = EmployerJob::query()->where('status', 'active');

        $query->where(function (Builder $outer) use ($rawQuery, $tokens) {
            $this->applySqlTextClauses($outer, $rawQuery, $tokens, [
                'title',
                'description',
                'company_name',
                'job_department',
                'location',
            ]);
            $outer->orWhereHas('user.referrerProfile', function (Builder $profile) use ($rawQuery, $tokens) {
                $this->applySqlTextClauses($profile, $rawQuery, $tokens, ['company_name']);
            });
        });

        return $query->orderByDesc('created_at')
            ->limit((int) config('elasticsearch.search_limit', 500))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    protected function searchJobRolesSql(string $rawQuery): array
    {
        $tokens = $this->tokenize($rawQuery);
        $query = JobRole::query()->active();

        $query->where(function (Builder $outer) use ($rawQuery, $tokens) {
            $this->applySqlTextClauses($outer, $rawQuery, $tokens, [
                'title',
                'description',
                'sector',
            ]);
        });

        return $query->orderByDesc('id')
            ->limit((int) config('elasticsearch.search_limit', 500))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  list<string>  $tokens
     * @param  list<string>  $columns
     */
    protected function applySqlTextClauses(Builder $query, string $rawQuery, array $tokens, array $columns): void
    {
        $like = '%'.$rawQuery.'%';
        $query->where(function (Builder $phrase) use ($columns, $like) {
            foreach ($columns as $i => $column) {
                if ($i === 0) {
                    $phrase->where($column, 'like', $like);
                } else {
                    $phrase->orWhere($column, 'like', $like);
                }
            }
        });

        foreach ($tokens as $token) {
            $tokenLike = '%'.$token.'%';
            $query->orWhere(function (Builder $word) use ($columns, $tokenLike) {
                foreach ($columns as $i => $column) {
                    if ($i === 0) {
                        $word->where($column, 'like', $tokenLike);
                    } else {
                        $word->orWhere($column, 'like', $tokenLike);
                    }
                }
            });
        }
    }

    /**
     * @param  list<int>  $ids
     */
    protected function orderByFieldIds(Builder $query, string $column, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $safe = implode(',', array_map('intval', $ids));
        $query->orderByRaw("FIELD({$column}, {$safe})");
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
            Log::warning('Failed to index job opening document.', [
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

    protected function employerCompanyName(EmployerJob $job): string
    {
        $name = trim((string) ($job->company_name ?? ''));
        if ($name !== '') {
            return $name;
        }

        return trim((string) ($job->user?->referrerProfile?->company_name ?? ''));
    }

    protected function plainText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @param  mixed  $skills
     */
    protected function skillsText($skills): string
    {
        if (! is_array($skills)) {
            return '';
        }

        $parts = [];
        foreach ($skills as $skill) {
            if (is_string($skill) && trim($skill) !== '') {
                $parts[] = trim($skill);
            } elseif (is_array($skill) && isset($skill['name'])) {
                $parts[] = trim((string) $skill['name']);
            }
        }

        return implode(' ', $parts);
    }

}
