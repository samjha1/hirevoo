<?php

namespace App\Observers;

use App\Models\EmployerJob;
use App\Services\JobOpeningsSearchService;

class EmployerJobSearchObserver
{
    public function __construct(
        protected JobOpeningsSearchService $search,
    ) {}

    public function saved(EmployerJob $job): void
    {
        $this->search->indexEmployerJob($job);
    }

    public function deleted(EmployerJob $job): void
    {
        $this->search->deleteEmployerJob($job);
    }
}
