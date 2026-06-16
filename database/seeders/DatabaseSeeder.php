<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EmployerPlanSeeder::class,
            CandidatePlanSeeder::class,
            PlanCouponSeeder::class,
            AdminSeeder::class,
            JobRoleSeeder::class,
            UpskillOpportunitySeeder::class,
            EmployerJobSeeder::class,
        ]);
    }
}
