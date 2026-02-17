<?php

namespace Database\Seeders;

use App\Models\JobRole;
use App\Models\JobRequiredSkill;
use Illuminate\Database\Seeder;

class JobRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['title' => 'Data Analyst', 'slug' => 'data-analyst', 'description' => 'Analyze data and build reports.'],
            ['title' => 'Software Engineer', 'slug' => 'software-engineer', 'description' => 'Design and develop software applications.'],
            ['title' => 'Product Manager', 'slug' => 'product-manager', 'description' => 'Own product roadmap and execution.'],
        ];

        foreach ($roles as $r) {
            $role = JobRole::firstOrCreate(
                ['slug' => $r['slug']],
                ['title' => $r['title'], 'description' => $r['description'], 'is_active' => true]
            );
            if ($role->wasRecentlyCreated && $role->title === 'Data Analyst') {
                JobRequiredSkill::insert([
                    ['job_role_id' => $role->id, 'skill_name' => 'SQL', 'priority' => 1],
                    ['job_role_id' => $role->id, 'skill_name' => 'Excel', 'priority' => 2],
                    ['job_role_id' => $role->id, 'skill_name' => 'Python', 'priority' => 3],
                    ['job_role_id' => $role->id, 'skill_name' => 'Data Visualization', 'priority' => 4],
                ]);
            }
        }
    }
}
