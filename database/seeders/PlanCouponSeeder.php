<?php

namespace Database\Seeders;

use App\Models\PlanCoupon;
use Illuminate\Database\Seeder;

class PlanCouponSeeder extends Seeder
{
    public function run(): void
    {
        PlanCoupon::query()->updateOrCreate(
            ['code' => 'HIREVO10'],
            [
                'description' => 'Launch discount — 10% off any plan',
                'discount_percent' => 10,
                'is_active' => true,
                'max_uses' => null,
                'uses_count' => 0,
                'valid_from' => null,
                'valid_until' => null,
                'applicable_plan_slugs' => null,
            ],
        );
    }
}
