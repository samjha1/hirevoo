<?php

namespace App\Console\Commands;

use Database\Seeders\SyntheticJobRoleSeeder;
use Illuminate\Console\Command;

class SeedSyntheticJobGoals extends Command
{
    protected $signature = 'hirevo:seed-synthetic-job-goals';

    protected $description = 'Seed 10,000 synthetic job goals for the catalog (skips if already present)';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => SyntheticJobRoleSeeder::class]);

        return self::SUCCESS;
    }
}
