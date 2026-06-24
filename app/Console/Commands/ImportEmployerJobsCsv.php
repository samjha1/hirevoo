<?php

namespace App\Console\Commands;

use App\Services\EmployerJobImportService;
use Illuminate\Console\Command;

class ImportEmployerJobsCsv extends Command
{
    protected $signature = 'hirevo:import-employer-jobs-csv
                            {path : Path to the CSV file}
                            {--employer=catalog-employer@hirevo.com : Employer user email to attach jobs to}
                            {--skip-duplicates : Skip rows that match an existing title + company for this employer}
                            {--reindex : Reindex Elasticsearch after import (slower; skipped by default)}';

    protected $description = 'Import employer job openings from a CSV file';

    public function handle(EmployerJobImportService $importService): int
    {
        $path = (string) $this->argument('path');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $employerEmail = (string) $this->option('employer');
        $employer = $importService->ensureCatalogEmployer($employerEmail);
        $this->info("Importing jobs for employer: {$employer->email} (user #{$employer->id})");

        try {
            $summary = $importService->importFromCsvFile(
                $path,
                $employer,
                (bool) $this->option('skip-duplicates'),
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Imported: {$summary['imported']}");
        $this->info("Skipped: {$summary['skipped']}");
        $this->info('Failed: '.count($summary['failed']));

        foreach ($summary['failed'] as $failure) {
            $this->warn("  Line {$failure['line']}: {$failure['message']}");
        }

        if ($this->option('reindex')) {
            $this->info('Reindexing job openings search…');
            $importService->reindexSearchIfEnabled();
        } else {
            $this->line('Tip: run php artisan hirevo:search-reindex if Elasticsearch search should include new jobs.');
        }

        return count($summary['failed']) > 0 && $summary['imported'] === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
