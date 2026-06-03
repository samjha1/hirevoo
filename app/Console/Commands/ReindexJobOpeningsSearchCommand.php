<?php

namespace App\Console\Commands;

use App\Services\JobOpeningsSearchService;
use App\Services\TalentPoolElasticsearchService;
use Illuminate\Console\Command;

class ReindexJobOpeningsSearchCommand extends Command
{
    protected $signature = 'hirevo:search-reindex {--force : Recreate the index if it already exists}';

    protected $description = 'Reindex job openings and employer talent pool for Elasticsearch search';

    public function handle(
        JobOpeningsSearchService $jobs,
        TalentPoolElasticsearchService $talentPool,
    ): int {
        if (! $jobs->isEnabled()) {
            $this->error('Elasticsearch is disabled. Set ELASTICSEARCH_ENABLED=true in .env and start Elasticsearch.');

            return self::FAILURE;
        }

        if ($this->option('force')) {
            $this->warn('Force recreate is not implemented; delete indices manually if you need a clean mapping.');
        }

        $this->info('Ensuring job openings index exists…');

        try {
            $jobs->ensureIndex();
        } catch (\Throwable $e) {
            $this->error('Could not connect to Elasticsearch: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Indexing job openings…');
        $jobCounts = $jobs->reindexAll();

        $this->info(sprintf(
            'Job openings: %d employer jobs, %d job roles.',
            $jobCounts['employer_jobs'],
            $jobCounts['job_roles']
        ));

        $this->info('Ensuring talent pool index exists…');

        try {
            $talentPool->ensureIndex();
        } catch (\Throwable $e) {
            $this->error('Could not prepare talent pool index: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Indexing talent pool candidates…');
        $talentCounts = $talentPool->reindexAll();

        $this->info(sprintf(
            'Talent pool: %d verified candidates, %d talent pool profiles.',
            $talentCounts['verified'],
            $talentCounts['talent_pool']
        ));

        $this->info('Reindex complete.');

        return self::SUCCESS;
    }
}
