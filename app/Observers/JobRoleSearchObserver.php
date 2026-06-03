<?php

namespace App\Observers;

use App\Models\JobRole;
use App\Services\JobOpeningsSearchService;

class JobRoleSearchObserver
{
    public function __construct(
        protected JobOpeningsSearchService $search,
    ) {}

    public function saved(JobRole $role): void
    {
        $this->search->indexJobRole($role);
    }

    public function deleted(JobRole $role): void
    {
        $this->search->deleteJobRole($role);
    }
}
