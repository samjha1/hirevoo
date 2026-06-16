<?php

namespace App\Console\Commands;

use App\Models\TalentPoolCandidate;
use App\Models\User;
use App\Services\JobOpeningsSearchService;
use App\Services\TalentPoolElasticsearchService;
use Illuminate\Console\Command;

class ReindexJobOpeningsSearchCommand extends Command
{
    protected $signature = 'hirevo:search-reindex
                            {--force : Delete and recreate the talent pool index (after mapping upgrades)}
                            {--talent-only : Reindex only the employer talent pool}';

    protected $description = 'Reindex job openings and employer talent pool for Elasticsearch search';

    public function handle(
        JobOpeningsSearchService $jobs,
        TalentPoolElasticsearchService $talentPool,
    ): int {
        if (! $jobs->isEnabled()) {
            $this->error('Elasticsearch is disabled. Set ELASTICSEARCH_ENABLED=true in .env and start Elasticsearch/OpenSearch.');

            return self::FAILURE;
        }

        if (! $talentPool->canUseElasticsearch()) {
            $hosts = implode(', ', config('elasticsearch.hosts', ['http://127.0.0.1:9200']));
            $this->error('Cannot connect to Elasticsearch at '.$hosts.'.');
            $this->line('Start Elasticsearch/OpenSearch, or set ELASTICSEARCH_ENABLED=false in .env to use SQL search locally.');

            return self::FAILURE;
        }

        $talentOnly = (bool) $this->option('talent-only');

        if (! $talentOnly) {
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
        }

        if ($this->option('force')) {
            $this->warn('Deleting talent pool index for clean recreate…');
            $talentPool->deleteIndexIfExists();
        }

        $this->info('Ensuring talent pool index exists…');

        try {
            $talentPool->ensureIndex();
        } catch (\Throwable $e) {
            $this->error('Could not prepare talent pool index: '.$e->getMessage());

            return self::FAILURE;
        }

        $verifiedTotal = User::query()
            ->where('role', 'candidate')
            ->where('status', 'active')
            ->whereHas('candidateProfile')
            ->count();
        $talentTotal = TalentPoolCandidate::query()->discoverable()->count();
        $total = $verifiedTotal + $talentTotal;

        $this->info(sprintf(
            'Indexing talent pool (%s verified + %s talent pool = %s documents)…',
            number_format($verifiedTotal),
            number_format($talentTotal),
            number_format($total)
        ));

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $bar->start();

        $talentCounts = $talentPool->reindexAll(function (int $step) use ($bar) {
            $bar->advance($step);
        });

        $bar->finish();
        $this->newLine(2);

        $this->info(sprintf(
            'Talent pool: %s verified, %s talent pool profiles indexed.',
            number_format($talentCounts['verified']),
            number_format($talentCounts['talent_pool'])
        ));

        $this->info('Reindex complete.');

        return self::SUCCESS;
    }
}
