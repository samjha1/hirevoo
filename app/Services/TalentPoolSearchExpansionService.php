<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TalentPoolSearchExpansionService
{
    public function __construct(
        protected GptService $gpt,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{sector: string, keywords: list<string>}|null
     */
    public function expandFromFilters(array $filters): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $query = $this->extractSearchText($filters);
        if ($query === '') {
            return null;
        }

        return $this->expandQuery($query);
    }

    /**
     * @return array{sector: string, keywords: list<string>}|null
     */
    public function expandQuery(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $ttl = max(300, (int) config('hirevo.talent_pool_related_search.cache_ttl', 86400));
        $cacheKey = 'tp_search_expand:'.hash('xxh128', mb_strtolower($query));

        return Cache::remember($cacheKey, $ttl, function () use ($query) {
            $static = $this->expandFromConfig($query);
            if ($static !== null) {
                return $static;
            }

            if ($this->gpt->isAvailable()) {
                $ai = $this->gpt->expandTalentPoolSearchTerms($query);
                if ($ai !== null) {
                    return $ai;
                }
            }

            return $this->expandFromDepartmentHints($query);
        });
    }

    /**
     * Known staffing/recruitment aliases (e.g. benchsales) — safe to skip slow exact SQL before related search.
     */
    public function hasStaticAliasMatch(string $query): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        return $this->expandFromConfig(trim($query)) !== null;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function extractSearchText(array $filters): string
    {
        $parts = array_filter([
            trim((string) ($filters['q'] ?? '')),
            trim((string) ($filters['skills'] ?? '')),
        ]);

        return trim(implode(' ', $parts));
    }

    protected function isEnabled(): bool
    {
        return filter_var(
            config('hirevo.talent_pool_related_search.enabled', true),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * @return array{sector: string, keywords: list<string>}|null
     */
    protected function expandFromConfig(string $query): ?array
    {
        $normalized = $this->normalizeForMatch($query);
        if ($normalized === '') {
            return null;
        }

        $best = null;
        $bestLen = 0;

        foreach (config('hirevo.talent_pool_related_search.aliases', []) as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            foreach ($entry['patterns'] ?? [] as $pattern) {
                $patternNorm = $this->normalizeForMatch((string) $pattern);
                if ($patternNorm === '') {
                    continue;
                }

                if (str_contains($normalized, $patternNorm) && mb_strlen($patternNorm) > $bestLen) {
                    $bestLen = mb_strlen($patternNorm);
                    $best = $entry;
                }
            }
        }

        if ($best === null) {
            return null;
        }

        return $this->normalizeExpansion(
            (string) ($best['sector'] ?? 'Related sector'),
            $best['keywords'] ?? [],
            $query
        );
    }

    /**
     * @return array{sector: string, keywords: list<string>}|null
     */
    protected function expandFromDepartmentHints(string $query): ?array
    {
        $normalized = $this->normalizeForMatch($query);
        if ($normalized === '') {
            return null;
        }

        $departments = config('hirevo.employer_job_departments', []);
        $hints = config('hirevo.talent_pool_related_search.department_hints', []);
        $skillGroups = config('hirevo.employer_job_skill_presets', []);
        $departmentGroups = config('hirevo.employer_job_department_skill_groups', []);

        foreach ($departments as $department) {
            $department = (string) $department;
            $deptNorm = $this->normalizeForMatch($department);
            $matched = $deptNorm !== '' && str_contains($normalized, $deptNorm);

            if (! $matched) {
                foreach ($hints[$department] ?? [] as $hint) {
                    $hintNorm = $this->normalizeForMatch((string) $hint);
                    if ($hintNorm !== '' && str_contains($normalized, $hintNorm)) {
                        $matched = true;
                        break;
                    }
                }
            }

            if (! $matched) {
                continue;
            }

            $keywords = [];
            foreach ($departmentGroups[$department] ?? [] as $groupTitle) {
                foreach ($skillGroups[$groupTitle] ?? [] as $skill) {
                    $keywords[] = (string) $skill;
                }
            }

            $keywords = array_merge($keywords, $hints[$department] ?? [], [$department]);

            return $this->normalizeExpansion($department, $keywords, $query);
        }

        $tokens = preg_split('/[\s,;]+/', mb_strtolower($query)) ?: [];
        $tokens = array_values(array_filter($tokens, fn (string $t): bool => mb_strlen($t) >= 3));

        if ($tokens === []) {
            return null;
        }

        return $this->normalizeExpansion(
            'Related profiles',
            array_slice($tokens, 0, (int) config('hirevo.talent_pool_related_search.max_keywords', 8)),
            $query
        );
    }

    /**
     * @param  list<string>  $keywords
     * @return array{sector: string, keywords: list<string>}|null
     */
    protected function normalizeExpansion(string $sector, array $keywords, string $originalQuery): ?array
    {
        $max = max(3, (int) config('hirevo.talent_pool_related_search.max_keywords', 8));
        $clean = [];
        $seen = [];

        foreach ($keywords as $keyword) {
            $keyword = trim((string) $keyword);
            if ($keyword === '') {
                continue;
            }

            $key = mb_strtolower($keyword);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $clean[] = $keyword;

            if (count($clean) >= $max) {
                break;
            }
        }

        $originalTokens = preg_split('/[\s,;]+/', mb_strtolower($originalQuery)) ?: [];
        foreach ($originalTokens as $token) {
            $token = trim($token);
            if (mb_strlen($token) < 3) {
                continue;
            }

            $key = mb_strtolower($token);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $clean[] = $token;

            if (count($clean) >= $max) {
                break;
            }
        }

        if ($clean === []) {
            return null;
        }

        $sector = trim($sector) !== '' ? trim($sector) : 'Related sector';

        return [
            'sector' => $sector,
            'keywords' => array_values($clean),
        ];
    }

    protected function normalizeForMatch(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
