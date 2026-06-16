<?php

namespace Database\Seeders;

use App\Models\CandidatePlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CandidatePlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = config('hirevo_candidate_plans.plans', []);
        $sort = 0;

        foreach ($plans as $slug => $plan) {
            CandidatePlan::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $plan['name'],
                    'tagline' => $plan['tagline'] ?? null,
                    'price_inr' => (int) $plan['price_inr'],
                    'cta' => 'Choose '.$plan['name'],
                    'is_popular' => (bool) ($plan['popular'] ?? false),
                    'is_active' => true,
                    'sort_order' => $sort++,
                    'duration_days' => (int) ($plan['duration_days'] ?? 30),
                    'referral_requests_limit' => (int) ($plan['referral_requests_limit'] ?? 3),
                    'features' => $plan['features'] ?? [],
                    'extras' => $plan['extras'] ?? [],
                ],
            );
        }

        Cache::forget('candidate_plans.catalog');
    }
}
