<?php

namespace App\Services;

use App\Models\CandidatePlan;
use App\Models\CandidateProfile;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CandidatePlanService
{
    public function findPlan(string $planKey): ?CandidatePlan
    {
        $planKey = strtolower(trim($planKey));
        if ($planKey === '') {
            return null;
        }

        if (! Schema::hasTable('candidate_plans')) {
            return null;
        }

        return CandidatePlan::query()
            ->where('slug', $planKey)
            ->where('is_active', true)
            ->first();
    }

    public function planKey(?CandidateProfile $profile): ?string
    {
        if ($profile === null) {
            return null;
        }

        $plan = strtolower(trim((string) ($profile->subscription_plan ?? '')));

        return $plan !== '' ? $plan : null;
    }

    public function basePlanSlug(): string
    {
        return strtolower(trim((string) config('hirevo_candidate_plans.base_plan_slug', 'access')));
    }

    public function activePlanKeyForUser(?User $user): ?string
    {
        if ($user === null || ! $user->isCandidate()) {
            return null;
        }

        $profile = $user->candidateProfile;
        $planKey = $this->planKey($profile);

        if ($planKey !== null) {
            return $planKey;
        }

        if (! Schema::hasTable('payments')) {
            return null;
        }

        $latest = Payment::query()
            ->where('user_id', $user->id)
            ->where('type', Payment::TYPE_PREMIUM_SUBSCRIPTION)
            ->where('status', Payment::STATUS_COMPLETED)
            ->orderByDesc('id')
            ->first();

        $fromPayment = strtolower(trim((string) ($latest?->meta['plan_key'] ?? '')));

        return $fromPayment !== '' ? $fromPayment : null;
    }

    public function planSortRank(string $planKey): int
    {
        $planKey = strtolower(trim($planKey));
        $plan = $this->findPlan($planKey);
        if ($plan !== null) {
            return (int) $plan->sort_order;
        }

        $slugs = array_keys(config('hirevo_candidate_plans.plans', []));
        $index = array_search($planKey, array_map('strtolower', $slugs), true);

        return $index === false ? 0 : (int) $index;
    }

    public function includesAiCareerTools(?string $planKey): bool
    {
        if ($planKey === null || trim($planKey) === '') {
            return false;
        }

        if ($this->planConfig($planKey) === null) {
            return false;
        }

        return $this->planSortRank($planKey) > $this->planSortRank($this->basePlanSlug());
    }

    public function planConfig(?string $planKey): ?array
    {
        if ($planKey === null || trim($planKey) === '') {
            return null;
        }

        $planKey = strtolower(trim($planKey));
        $plan = $this->findPlan($planKey);
        if ($plan !== null) {
            return array_merge($plan->toDisplayArray(), ['key' => $plan->slug]);
        }

        $legacy = config("hirevo_candidate_plans.plans.{$planKey}");

        return is_array($legacy) ? array_merge($legacy, ['key' => $planKey]) : null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allPlansKeyed(): array
    {
        if (! Schema::hasTable('candidate_plans')) {
            return config('hirevo_candidate_plans.plans', []);
        }

        return Cache::remember('candidate_plans.catalog', 300, function (): array {
            $plans = CandidatePlan::query()->active()->ordered()->get();
            if ($plans->isEmpty()) {
                return config('hirevo_candidate_plans.plans', []);
            }

            return $plans
                ->keyBy('slug')
                ->map(fn (CandidatePlan $plan) => array_merge($plan->toDisplayArray(), ['key' => $plan->slug]))
                ->all();
        });
    }

    /**
     * @return list<string>
     */
    public function purchasableSlugs(): array
    {
        return array_keys($this->allPlansKeyed());
    }

    public function clearPlanCache(): void
    {
        Cache::forget('candidate_plans.catalog');
    }

    public function hasActiveSubscription(?CandidateProfile $profile): bool
    {
        if ($profile === null || ! $profile->is_premium) {
            return false;
        }

        if ($profile->premium_expires_at !== null && $profile->premium_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * @return array{key: string, name: string, expires_at: ?\Carbon\Carbon, started_at: ?\Carbon\Carbon}|null
     */
    public function activeSubscriptionSummary(?User $user): ?array
    {
        if ($user === null || ! $user->isCandidate()) {
            return null;
        }

        $profile = $user->candidateProfile;
        if (! $this->hasActiveSubscription($profile)) {
            return null;
        }

        $planKey = $this->activePlanKeyForUser($user);

        $planConfig = $this->planConfig($planKey);
        $name = $planConfig['name'] ?? ($planKey ? ucfirst(str_replace('-', ' ', $planKey)) : 'Premium');

        return [
            'key' => $planKey ?? 'premium',
            'name' => $name,
            'expires_at' => $profile->premium_expires_at,
            'started_at' => $profile->subscription_started_at,
            'renewal_plan' => $this->renewalPlanKey($profile),
        ];
    }

    public function renewalPlanKey(?CandidateProfile $profile): ?string
    {
        if ($profile === null) {
            return null;
        }

        $key = strtolower(trim((string) ($profile->renewal_plan ?? '')));

        return $key !== '' ? $key : null;
    }

    public function scheduleRenewalPlan(User $user, string $planKey): CandidateProfile
    {
        if (! $user->isCandidate()) {
            throw new \InvalidArgumentException('Only candidate accounts can schedule plan changes.');
        }

        $planKey = strtolower(trim($planKey));
        if ($this->planConfig($planKey) === null) {
            throw new \InvalidArgumentException('Invalid plan selected.');
        }

        $profile = $user->candidateProfile;
        if (! $this->hasActiveSubscription($profile)) {
            throw new \InvalidArgumentException('You need an active plan before scheduling a switch.');
        }

        $currentKey = $this->planKey($profile);
        if ($currentKey === $planKey) {
            throw new \InvalidArgumentException('This is already your current plan.');
        }

        $profile->update(['renewal_plan' => $planKey]);

        return $profile->fresh();
    }

    public function clearRenewalPlan(User $user): CandidateProfile
    {
        if (! $user->isCandidate()) {
            throw new \InvalidArgumentException('Only candidate accounts can manage plan changes.');
        }

        $profile = $user->candidateProfile ?? throw new \InvalidArgumentException('Profile not found.');

        $profile->update(['renewal_plan' => null]);

        return $profile->fresh();
    }
}
