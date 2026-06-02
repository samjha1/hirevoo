<?php

namespace Database\Seeders;

use App\Models\JobRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyntheticJobRoleSeeder extends Seeder
{
    public const TARGET_COUNT = 10000;

    public function run(): void
    {
        $existing = JobRole::where('is_synthetic', true)->count();
        if ($existing >= self::TARGET_COUNT) {
            $this->command?->info("Synthetic job goals already seeded ({$existing}). Skipping.");

            return;
        }

        $needed = self::TARGET_COUNT - $existing;
        $this->command?->info("Seeding {$needed} synthetic job goals…");

        $sectors = config('job_goal_sectors', []);
        $levelPrefixes = ['Junior', 'Senior', 'Lead', 'Associate', 'Principal', 'Staff', 'Regional', 'Global', ''];
        $levelSuffixes = ['', ' I', ' II', ' III', ' (Remote)', ' (Hybrid)', ' (Contract)', ' (Fresher)'];

        $templates = [];
        foreach ($sectors as $sectorKey => $sector) {
            foreach ($sector['titles'] as $title) {
                $templates[] = [
                    'sector' => $sectorKey,
                    'title' => $title,
                    'label' => $sector['label'] ?? $sectorKey,
                ];
            }
        }

        if ($templates === []) {
            $this->command?->error('No sector templates found in config/job_goal_sectors.php');

            return;
        }

        $usedSlugs = JobRole::whereNotNull('slug')->pluck('slug')->flip()->all();
        $now = now();
        $batch = [];
        $created = 0;
        $templateIndex = 0;
        $attempts = 0;
        $maxAttempts = $needed * 3;

        while ($created < $needed && $attempts < $maxAttempts) {
            $attempts++;
            $tpl = $templates[$templateIndex % count($templates)];
            $templateIndex++;

            $prefix = $levelPrefixes[array_rand($levelPrefixes)];
            $suffix = $levelSuffixes[array_rand($levelSuffixes)];
            $title = trim($prefix.' '.$tpl['title'].$suffix);
            $title = preg_replace('/\s+/', ' ', $title) ?? $title;

            $slugBase = Str::slug($title);
            $slug = $slugBase;
            $n = 1;
            while (isset($usedSlugs[$slug])) {
                $slug = $slugBase.'-'.$n;
                $n++;
            }
            $usedSlugs[$slug] = true;

            $sectorLabel = $tpl['label'];
            $batch[] = [
                'title' => $title,
                'slug' => $slug,
                'description' => "Explore {$title} opportunities across {$sectorLabel}. Build skills, compare your profile, and apply to aligned openings on Hirevo.",
                'is_active' => true,
                'is_synthetic' => true,
                'sector' => $tpl['sector'],
                'open_roles_count' => random_int(1200, 24800),
                'referral_boost_pct' => random_int(72, 88),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $created++;

            if (count($batch) >= 500) {
                DB::table('job_roles')->insert($batch);
                $batch = [];
                $this->command?->info("  …{$created} / {$needed}");
            }
        }

        if ($batch !== []) {
            DB::table('job_roles')->insert($batch);
        }

        $this->command?->info('Done. Total synthetic job goals: '.JobRole::where('is_synthetic', true)->count());
    }
}
