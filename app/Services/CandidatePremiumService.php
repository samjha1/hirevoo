<?php

namespace App\Services;

use App\Models\CandidateProfile;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class CandidatePremiumService
{
    public function __construct(
        private readonly CandidatePlanService $plans,
    ) {}

    public function hasAccess(?User $user): bool
    {
        if ($user === null || ! $user->isCandidate()) {
            return false;
        }

        if ($this->plans->hasActiveSubscription($user->candidateProfile)) {
            return true;
        }

        return $this->hasCompletedPremiumPayment($user);
    }

    public function hasAiCareerToolsAccess(?User $user): bool
    {
        if (! $this->hasAccess($user)) {
            return false;
        }

        return $this->plans->includesAiCareerTools($this->plans->activePlanKeyForUser($user));
    }

    /**
     * @return array{key: string, name: string, expires_at: ?\Carbon\Carbon, started_at: ?\Carbon\Carbon}|null
     */
    public function activeSubscriptionSummary(?User $user): ?array
    {
        return $this->plans->activeSubscriptionSummary($user);
    }

    public function planUrl(): string
    {
        return route('pricing');
    }

    private function hasCompletedPremiumPayment(User $user): bool
    {
        if (! Schema::hasTable('payments')) {
            return false;
        }

        return Payment::query()
            ->where('user_id', $user->id)
            ->where('type', Payment::TYPE_PREMIUM_SUBSCRIPTION)
            ->where('status', Payment::STATUS_COMPLETED)
            ->exists();
    }
}
