<?php

namespace App\Observers;

use App\Models\TalentPoolCandidate;
use App\Services\TalentPoolElasticsearchService;

class TalentPoolCandidateSearchObserver
{
    public function __construct(
        protected TalentPoolElasticsearchService $search,
    ) {}

    public function saved(TalentPoolCandidate $candidate): void
    {
        $this->search->indexTalentPoolCandidate($candidate);
    }

    public function deleted(TalentPoolCandidate $candidate): void
    {
        $this->search->deleteTalentPoolCandidate($candidate);
    }
}
