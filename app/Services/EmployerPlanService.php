<?php

namespace App\Services;

use App\Models\EmployerPlan;
use App\Models\ReferrerProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class EmployerPlanService
{
    public function planKey(?ReferrerProfile $profile): ?string
    {
        if ($profile === null) {
            return null;
        }

        $plan = strtolower(trim((string) ($profile->subscription_plan ?? '')));

        return $plan !== '' ? $plan : null;
    }

    public function findPlan(string $planKey): ?EmployerPlan
    {
        $planKey = strtolower(trim($planKey));
        if ($planKey === '') {
            return null;
        }

        return EmployerPlan::query()
            ->where('slug', $planKey)
            ->where('is_active', true)
            ->first();
    }

    public function planConfig(?string $planKey): ?array
    {
        if ($planKey === null || trim($planKey) === '') {
            return null;
        }

        $planKey = strtolower(trim($planKey));
        $plan = $this->findPlan($planKey);
        if ($plan !== null) {
            return $plan->toDisplayArray();
        }

        $legacy = config("hirevo_plans.plans.{$planKey}");

        return is_array($legacy) ? $legacy : null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allPlansKeyed(): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('employer_plans')) {
            return config('hirevo_plans.plans', []);
        }

        return Cache::remember('employer_plans.catalog', 300, function (): array {
            $plans = EmployerPlan::query()->active()->ordered()->get();
            if ($plans->isEmpty()) {
                return config('hirevo_plans.plans', []);
            }

            return $plans
                ->keyBy('slug')
                ->map(fn (EmployerPlan $plan) => $plan->toDisplayArray())
                ->all();
        });
    }

    public function clearPlanCache(): void
    {
        Cache::forget('employer_plans.catalog');
    }

    public function hasActiveSubscription(?ReferrerProfile $profile): bool
    {
        $key = $this->planKey($profile);
        if ($key === null) {
            return false;
        }

        if ($profile->subscription_expires_at && $profile->subscription_expires_at->isPast()) {
            return false;
        }

        return $this->planConfig($key) !== null;
    }

    /**
     * Talent Pool — approved employers can browse; phone/download use pool tokens.
     */
    public function canAccessTalentPool(?ReferrerProfile $profile): bool
    {
        return $profile !== null;
    }

    /** Credits on referrer profile are used for job postings only. */
    public function jobPostingCredits(?ReferrerProfile $profile): int
    {
        return max(0, (int) ($profile?->credits ?? 0));
    }

    /** @deprecated Use jobPostingCredits() */
    public function databaseCredits(?ReferrerProfile $profile): int
    {
        return $this->jobPostingCredits($profile);
    }

    public function planPriceRank(?string $planKey): int
    {
        $plan = $this->findPlan((string) $planKey);
        if ($plan !== null) {
            if ($plan->is_custom_price || $plan->price_inr === null) {
                return PHP_INT_MAX;
            }

            return (int) $plan->price_inr;
        }

        $configPlan = $this->planConfig($planKey);
        if ($configPlan === null) {
            return 0;
        }

        if (! empty($configPlan['custom_price'])) {
            return PHP_INT_MAX;
        }

        return (int) ($configPlan['price_inr'] ?? 0);
    }

    /**
     * @return list<int>
     */
    public function billingDurationOptions(?array $planConfig = null): array
    {
        if ($planConfig !== null && $this->isLaunchPlan($planConfig)) {
            return [1];
        }

        $options = config('hirevo_plans.billing_duration_options', [1, 3, 6, 12]);

        return array_values(array_filter(
            array_map('intval', is_array($options) ? $options : []),
            fn (int $months) => $months > 0,
        ));
    }

    public function defaultBillingMonths(?array $planConfig = null): int
    {
        $options = $this->billingDurationOptions($planConfig);
        $default = (int) config('hirevo_plans.default_billing_months', 1);

        return in_array($default, $options, true) ? $default : ($options[0] ?? 1);
    }

    /**
     * @param  array<string, mixed>  $planConfig
     */
    public function isLaunchPlan(array $planConfig): bool
    {
        return ! empty($planConfig['extras']['is_launch_offer'])
            || in_array($planConfig['billing_period'] ?? '', ['one_time_7d', 'one_time_30d', 'launch_7d'], true);
    }

    public function resolveBillingMonths(?int $billingMonths, ?array $planConfig = null): int
    {
        $options = $this->billingDurationOptions($planConfig);
        $months = $billingMonths ?? $this->defaultBillingMonths($planConfig);

        if (! in_array($months, $options, true)) {
            throw new InvalidArgumentException('Please choose a valid subscription duration.');
        }

        return $months;
    }

    public function activateSubscription(
        ReferrerProfile $profile,
        string $planKey,
        ?Carbon $expiresAt = null,
        ?int $billingMonths = null,
    ): void {
        $planKey = strtolower(trim($planKey));
        $plan = $this->findPlan($planKey);
        $planConfig = $plan?->toDisplayArray() ?? $this->planConfig($planKey);

        if ($plan === null && $planConfig === null) {
            throw new InvalidArgumentException("Unknown plan: {$planKey}");
        }

        $startsAt = now();
        if ($expiresAt === null) {
            $period = $plan?->billing_period ?? ($planConfig['billing_period'] ?? 'monthly');
            $months = $this->resolveBillingMonths($billingMonths, is_array($planConfig) ? $planConfig : null);
            $expiresAt = match ($period) {
                'yearly', 'annual' => $startsAt->copy()->addYear(),
                'one_time_7d', 'launch_7d' => $startsAt->copy()->addDays(7),
                'one_time_30d' => $startsAt->copy()->addDays(30),
                default => $startsAt->copy()->addMonths($months),
            };
        }

        $profile->update([
            'subscription_plan' => $planKey,
            'subscription_started_at' => $startsAt,
            'subscription_expires_at' => $expiresAt,
        ]);

        $this->clearPlanCache();
    }

    /**
     * Add job posting credits bundled with a purchased plan.
     */
    public function grantPlanJobCredits(ReferrerProfile $profile, string $planKey): int
    {
        $plan = $this->findPlan($planKey);
        $credits = $plan?->job_credits_included;

        if ($credits === null) {
            $config = $this->planConfig($planKey);
            $legacy = $config['job_credits_included'] ?? $config['database_credits_included'] ?? null;
            $credits = is_numeric($legacy) ? (int) $legacy : 0;
        }

        $credits = max(0, (int) $credits);
        if ($credits > 0) {
            $profile->increment('credits', $credits);
        }

        return $credits;
    }

    public function grantPlanTalentPoolTokens(ReferrerProfile $profile, string $planKey): int
    {
        return app(TalentPoolTokenService::class)->grantPlanTokens($profile, $planKey);
    }

    public function talentPoolTokens(?ReferrerProfile $profile): int
    {
        return app(TalentPoolTokenService::class)->tokens($profile);
    }

    public function canViewCandidate(User $employer, string $source, int $sourceId): bool
    {
        return app(TalentPoolTokenService::class)->canViewContact($employer, $source, $sourceId);
    }

    public function canDownloadCandidate(User $employer, string $source, int $sourceId): bool
    {
        return app(TalentPoolTokenService::class)->canDownload($employer, $source, $sourceId);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function enrichCandidateRow(array $row, User $employer, bool $previewMode = true): array
    {
        $source = (string) ($row['source'] ?? '');
        $sourceId = (int) ($row['source_id'] ?? 0);
        $row['is_unlocked'] = $this->canViewCandidate($employer, $source, $sourceId);
        $row['is_locked'] = ! $row['is_unlocked'];
        $row['can_download'] = $this->canDownloadCandidate($employer, $source, $sourceId);

        return $this->maskCandidateRow($row, $employer, $previewMode);
    }

    public function maskCandidateRow(array $row, User $employer, bool $previewMode = true): array
    {
        if ($this->canViewCandidate($employer, (string) ($row['source'] ?? ''), (int) ($row['source_id'] ?? 0))) {
            $row['is_unlocked'] = true;
            $row['is_locked'] = false;

            return $row;
        }

        $row['is_unlocked'] = false;
        $row['is_locked'] = true;
        $row['full_name'] = $this->maskName((string) ($row['full_name'] ?? 'Candidate'));
        $row['email'] = null;
        $row['phone'] = null;
        $row['resume_url'] = null;
        $row['has_resume'] = (bool) ($row['has_resume'] ?? false);
        $row['profile_image'] = null;
        $row['linkedin_url'] = null;
        $row['github_url'] = null;
        $row['portfolio_url'] = null;
        $row['profile_summary'] = $previewMode
            ? \Illuminate\Support\Str::limit((string) ($row['profile_summary'] ?? ''), 80)
            : null;
        $row['current_role'] = $previewMode ? $this->maskText((string) ($row['current_role'] ?? $row['title'] ?? '')) : null;
        $row['previous_role'] = null;

        if (is_array($row['skills'] ?? null)) {
            $row['skills'] = array_slice($row['skills'], 0, $previewMode ? 4 : 0);
        }

        return $row;
    }

    protected function maskName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        if ($parts === []) {
            return 'Candidate';
        }

        $first = $parts[0];
        $lastInitial = isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)).'.' : '';

        return trim($first.' '.$lastInitial);
    }

    protected function maskText(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        return \Illuminate\Support\Str::limit($text, 40);
    }
}
