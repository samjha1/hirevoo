<?php

namespace App\Services;

use App\Models\EmployerTalentPoolAction;
use App\Models\EmployerTalentPoolUnlock;
use App\Models\ReferrerProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TalentPoolTokenService
{
    public function __construct(
        protected EmployerPlanService $planService,
    ) {}

    public function tokens(?ReferrerProfile $profile): int
    {
        return max(0, (int) ($profile?->talent_pool_tokens ?? 0));
    }

    public function viewCost(): int
    {
        return max(1, (int) config('hirevo_plans.unlock_credit_cost', 1));
    }

    public function downloadCost(): int
    {
        return max(1, (int) config('hirevo_plans.excel_download_credit_cost', 2));
    }

    public function hasUnlimitedUnlocks(?ReferrerProfile $profile): bool
    {
        if (! $this->planService->hasActiveSubscription($profile)) {
            return false;
        }

        $planKey = $this->planService->planKey($profile);
        $config = $planKey ? $this->planService->planConfig($planKey) : null;

        return (bool) ($config['unlimited_profile_unlocks'] ?? false);
    }

    public function isProfileViewUnlocked(int $employerUserId, string $source, int $sourceId): bool
    {
        return EmployerTalentPoolUnlock::query()
            ->where('employer_user_id', $employerUserId)
            ->where('candidate_source', $source)
            ->where('candidate_ref_id', $sourceId)
            ->exists();
    }

    public function isDownloadUnlocked(int $employerUserId, string $source, int $sourceId): bool
    {
        return EmployerTalentPoolUnlock::query()
            ->where('employer_user_id', $employerUserId)
            ->where('candidate_source', $source)
            ->where('candidate_ref_id', $sourceId)
            ->whereNotNull('downloaded_at')
            ->exists();
    }

    public function canViewContact(User $employer, string $source, int $sourceId): bool
    {
        $profile = $employer->referrerProfile;
        if (! $this->planService->canAccessTalentPool($profile)) {
            return false;
        }

        if ($this->hasUnlimitedUnlocks($profile)) {
            return true;
        }

        return $this->isProfileViewUnlocked($employer->id, $source, $sourceId);
    }

    public function canDownload(User $employer, string $source, int $sourceId): bool
    {
        $profile = $employer->referrerProfile;
        if (! $this->planService->canAccessTalentPool($profile)) {
            return false;
        }

        if ($this->hasUnlimitedUnlocks($profile)) {
            return true;
        }

        return $this->isDownloadUnlocked($employer->id, $source, $sourceId);
    }

    /**
     * @return array{ok: bool, error?: string, tokens_remaining?: int, tokens_required?: int, already_unlocked?: bool, unlimited?: bool}
     */
    public function unlockProfileView(User $employer, string $source, int $sourceId): array
    {
        $profile = $employer->referrerProfile;
        if (! $profile || ! $this->planService->canAccessTalentPool($profile)) {
            return ['ok' => false, 'error' => 'subscription_required'];
        }

        if (! EmployerTalentPoolAction::validSource($source)) {
            return ['ok' => false, 'error' => 'invalid_source'];
        }

        if ($this->isProfileViewUnlocked($employer->id, $source, $sourceId)) {
            return [
                'ok' => true,
                'already_unlocked' => true,
                'tokens_remaining' => $this->tokens($profile),
            ];
        }

        if ($this->hasUnlimitedUnlocks($profile)) {
            $this->recordViewUnlock($employer->id, $source, $sourceId, 0);

            return [
                'ok' => true,
                'unlimited' => true,
                'tokens_remaining' => $this->tokens($profile),
            ];
        }

        $cost = $this->viewCost();
        if ($this->tokens($profile) < $cost) {
            return [
                'ok' => false,
                'error' => 'insufficient_tokens',
                'tokens_remaining' => $this->tokens($profile),
                'tokens_required' => $cost,
            ];
        }

        DB::transaction(function () use ($employer, $source, $sourceId, $cost, $profile): void {
            ReferrerProfile::query()
                ->whereKey($profile->id)
                ->where('talent_pool_tokens', '>=', $cost)
                ->decrement('talent_pool_tokens', $cost);

            $this->recordViewUnlock($employer->id, $source, $sourceId, $cost);
        });

        return [
            'ok' => true,
            'tokens_remaining' => $this->tokens($profile->fresh()),
            'tokens_spent' => $cost,
        ];
    }

    /**
     * @return array{ok: bool, error?: string, tokens_remaining?: int, tokens_required?: int, already_downloaded?: bool, unlimited?: bool}
     */
    public function unlockDownload(User $employer, string $source, int $sourceId): array
    {
        $profile = $employer->referrerProfile;
        if (! $profile || ! $this->planService->canAccessTalentPool($profile)) {
            return ['ok' => false, 'error' => 'subscription_required'];
        }

        if (! EmployerTalentPoolAction::validSource($source)) {
            return ['ok' => false, 'error' => 'invalid_source'];
        }

        if ($this->isDownloadUnlocked($employer->id, $source, $sourceId)) {
            return [
                'ok' => true,
                'already_downloaded' => true,
                'tokens_remaining' => $this->tokens($profile),
            ];
        }

        if ($this->hasUnlimitedUnlocks($profile)) {
            $this->recordDownloadUnlock($employer->id, $source, $sourceId, 0);

            return [
                'ok' => true,
                'unlimited' => true,
                'tokens_remaining' => $this->tokens($profile),
            ];
        }

        $cost = $this->downloadCost();
        if ($this->tokens($profile) < $cost) {
            return [
                'ok' => false,
                'error' => 'insufficient_tokens',
                'tokens_remaining' => $this->tokens($profile),
                'tokens_required' => $cost,
            ];
        }

        DB::transaction(function () use ($employer, $source, $sourceId, $cost, $profile): void {
            ReferrerProfile::query()
                ->whereKey($profile->id)
                ->where('talent_pool_tokens', '>=', $cost)
                ->decrement('talent_pool_tokens', $cost);

            $this->recordDownloadUnlock($employer->id, $source, $sourceId, $cost);
        });

        return [
            'ok' => true,
            'tokens_remaining' => $this->tokens($profile->fresh()),
            'tokens_spent' => $cost,
        ];
    }

    public function grantPlanTokens(ReferrerProfile $profile, string $planKey): int
    {
        $config = $this->planService->planConfig($planKey);
        $tokens = $config['database_credits_included'] ?? null;

        if ($tokens === null || ! is_numeric($tokens)) {
            return 0;
        }

        $tokens = max(0, (int) $tokens);
        if ($tokens > 0) {
            $profile->increment('talent_pool_tokens', $tokens);
        }

        return $tokens;
    }

    protected function recordViewUnlock(int $employerUserId, string $source, int $sourceId, int $tokensSpent): void
    {
        EmployerTalentPoolUnlock::query()->updateOrCreate(
            [
                'employer_user_id' => $employerUserId,
                'candidate_source' => $source,
                'candidate_ref_id' => $sourceId,
            ],
            [
                'credits_spent' => max(1, $tokensSpent > 0 ? $tokensSpent : 1),
            ]
        );
    }

    protected function recordDownloadUnlock(int $employerUserId, string $source, int $sourceId, int $tokensSpent): void
    {
        $unlock = EmployerTalentPoolUnlock::query()->firstOrNew([
            'employer_user_id' => $employerUserId,
            'candidate_source' => $source,
            'candidate_ref_id' => $sourceId,
        ]);

        if (! $unlock->exists) {
            $unlock->credits_spent = 0;
        }

        $unlock->download_tokens_spent = max($unlock->download_tokens_spent, $tokensSpent);
        $unlock->downloaded_at = now();
        $unlock->save();
    }
}
