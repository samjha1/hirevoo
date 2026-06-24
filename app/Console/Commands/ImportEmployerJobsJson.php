<?php

namespace App\Console\Commands;

use App\Services\EmployerJobImportService;
use Illuminate\Console\Command;

class ImportEmployerJobsJson extends Command
{
    protected $signature = 'hirevo:import-employer-jobs-json
                            {path : Path to the JSON file (array of job objects)}
                            {--employer=catalog-employer@hirevo.com : Employer user email to attach jobs to}
                            {--skip-duplicates : Skip rows that match an existing title + company for this employer}
                            {--reindex : Reindex Elasticsearch after import (slower; skipped by default)}';

    protected $description = 'Import employer job openings from a JSON file';

    public function handle(EmployerJobImportService $importService): int
    {
        $path = (string) $this->argument('path');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            $this->error("Could not read file: {$path}");

            return self::FAILURE;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            $this->error('JSON must be an array of job objects.');

            return self::FAILURE;
        }

        $rows = array_is_list($decoded) ? $decoded : [$decoded];

        $employerEmail = (string) $this->option('employer');
        $employer = $importService->ensureCatalogEmployer($employerEmail);
        $this->info("Importing jobs for employer: {$employer->email} (user #{$employer->id})");

        $summary = $importService->importFromArray(
            $rows,
            $employer,
            (bool) $this->option('skip-duplicates'),
        );

        $this->info("Imported: {$summary['imported']}");
        $this->info("Skipped: {$summary['skipped']}");
        $this->info('Failed: '.count($summary['failed']));

        foreach ($summary['failed'] as $failure) {
            $this->warn("  Row {$failure['line']}: {$failure['message']}");
        }

        if ($this->option('reindex')) {
            $this->info('Reindexing job openings search…');
            $importService->reindexSearchIfEnabled();
        }

        return count($summary['failed']) > 0 && $summary['imported'] === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
