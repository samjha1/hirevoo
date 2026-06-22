<?php

namespace App\Support;

class TalentPoolSalary
{
    /**
     * @return list<array{label: string, min_lpa: int}>
     */
    public static function buckets(): array
    {
        return [
            ['label' => '3+ LPA', 'min_lpa' => 3],
            ['label' => '5+ LPA', 'min_lpa' => 5],
            ['label' => '10+ LPA', 'min_lpa' => 10],
        ];
    }

    public static function minAnnualInr(int $minLpa): int
    {
        return max(0, $minLpa) * 100_000;
    }

    public static function isAllowedMinLpa(mixed $value): bool
    {
        return in_array((int) $value, array_column(self::buckets(), 'min_lpa'), true);
    }

    /**
     * Parse free-text expected salary to annual INR (best effort).
     */
    public static function parseAnnualInr(
        ?string $amount,
        ?string $currency = 'INR',
        ?string $period = 'per_annum',
    ): ?int {
        if ($amount === null || trim($amount) === '' || trim($amount) === '0') {
            return null;
        }

        $raw = mb_strtolower(trim($amount));
        $raw = preg_replace('/^(inr|rs\.?|₹)\s*/u', '', $raw) ?? $raw;
        $raw = str_replace([',', ' '], '', $raw);

        if (preg_match('/(\d+(?:\.\d+)?)[-–to]+(\d+(?:\.\d+)?)(lpa|lakh|lac|lp)/', $raw, $match)) {
            $avg = ((float) $match[1] + (float) $match[2]) / 2;

            return (int) round($avg * 100_000);
        }

        if (preg_match('/(\d+(?:\.\d+)?)\+?(lpa|lakh|lac|lp)/', $raw, $match)) {
            return (int) round((float) $match[1] * 100_000);
        }

        if (preg_match('/(\d+(?:\.\d+)?)\+/', $raw, $match)) {
            $value = (float) $match[1];

            return $value < 100
                ? (int) round($value * 100_000)
                : (int) round($value);
        }

        $digits = (int) preg_replace('/\D/', '', $raw);
        if ($digits <= 0) {
            return null;
        }

        if (str_contains($raw, 'lpa') || str_contains($raw, 'lakh') || str_contains($raw, 'lac')) {
            return $digits < 1_000
                ? $digits * 100_000
                : $digits;
        }

        if (($period ?? 'per_annum') === 'per_month') {
            return $digits * 12;
        }

        if ($digits >= 100_000) {
            return $digits;
        }

        if ($digits >= 1_000 && $digits < 100_000) {
            return $digits * 12;
        }

        if ($digits < 100) {
            return (int) ($digits * 100_000);
        }

        return $digits;
    }

    public static function meetsMinimumLpa(
        ?string $amount,
        int $minLpa,
        ?string $currency = 'INR',
        ?string $period = 'per_annum',
    ): bool {
        $annual = self::parseAnnualInr($amount, $currency, $period);
        if ($annual === null) {
            return false;
        }

        return $annual >= self::minAnnualInr($minLpa);
    }
}
