<?php

namespace App\Services;

use App\Models\ReferrerProfile;
use App\Models\User;
use Carbon\Carbon;
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

    public function planConfig(?string $planKey): ?array
    {
        if ($planKey === null) {
            return null;
        }

        return config("hirevo_plans.plans.{$planKey}");
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
     * Talent Pool resume database — requires any active subscription (not credits).
     */
    public function canAccessTalentPool(?ReferrerProfile $profile): bool
    {
        return $this->hasActiveSubscription($profile);
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
        $plan = $this->planConfig($planKey);
        if ($plan === null) {
            return 0;
        }

        if (! empty($plan['custom_price'])) {
            return PHP_INT_MAX;
        }

        return (int) ($plan['price_inr'] ?? 0);
    }

    public function activateSubscription(ReferrerProfile $profile, string $planKey, ?Carbon $expiresAt = null): void
    {
        $planKey = strtolower(trim($planKey));

        if ($this->planConfig($planKey) === null) {
            throw new InvalidArgumentException("Unknown plan: {$planKey}");
        }

        $startsAt = now();
        $expiresAt ??= $startsAt->copy()->addMonth();

        $profile->update([
            'subscription_plan' => $planKey,
            'subscription_started_at' => $startsAt,
            'subscription_expires_at' => $expiresAt,
        ]);
    }

    public function canViewCandidate(User $employer, string $source, int $sourceId): bool
    {
        return $this->canAccessTalentPool($employer->referrerProfile);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
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
