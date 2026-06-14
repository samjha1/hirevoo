<?php

namespace App\Support;

class TalentPoolDisplay
{
    public static function multiplier(): int
    {
        return max(1, (int) config('hirevo.talent_pool_display_count_multiplier', 10));
    }

    public static function count(int $count): int
    {
        return $count * self::multiplier();
    }

    /**
     * @param  array{locations?: list<array{label: string, count: int}>, education?: list<array{label: string, count: int}>, experience?: list<array{label: string, count: int}>}  $facets
     * @return array{locations: list<array{label: string, count: int}>, education: list<array{label: string, count: int}>, experience: list<array{label: string, count: int}>}
     */
    public static function applyFacetCounts(array $facets): array
    {
        $keys = ['locations', 'education', 'experience'];

        foreach ($keys as $key) {
            $facets[$key] = array_map(function (array $facet): array {
                $facet['count'] = self::count((int) ($facet['count'] ?? 0));

                return $facet;
            }, $facets[$key] ?? []);
        }

        return [
            'locations' => $facets['locations'] ?? [],
            'education' => $facets['education'] ?? [],
            'experience' => $facets['experience'] ?? [],
        ];
    }
}
