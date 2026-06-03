<?php

namespace App\Observers;

use App\Models\User;
use App\Services\TalentPoolElasticsearchService;

class CandidateUserSearchObserver
{
    public function __construct(
        protected TalentPoolElasticsearchService $search,
    ) {}

    public function saved(User $user): void
    {
        if ($user->role !== 'candidate') {
            return;
        }

        $this->search->indexVerifiedUser($user);
    }

    public function deleted(User $user): void
    {
        if ($user->role !== 'candidate') {
            return;
        }

        $this->search->deleteVerifiedUser($user);
    }
}
