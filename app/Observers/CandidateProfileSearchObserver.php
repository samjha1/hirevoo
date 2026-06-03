<?php

namespace App\Observers;

use App\Models\CandidateProfile;
use App\Services\TalentPoolElasticsearchService;

class CandidateProfileSearchObserver
{
    public function __construct(
        protected TalentPoolElasticsearchService $search,
    ) {}

    public function saved(CandidateProfile $profile): void
    {
        $user = $profile->user;
        if ($user) {
            $this->search->indexVerifiedUser($user);
        }
    }

    public function deleted(CandidateProfile $profile): void
    {
        $user = $profile->user;
        if ($user) {
            $this->search->deleteVerifiedUser($user);
        }
    }
}
