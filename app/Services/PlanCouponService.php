<?php

namespace App\Services;

use App\Models\PlanCoupon;
use InvalidArgumentException;

class PlanCouponService
{
    public function findByCode(string $code): ?PlanCoupon
    {
        $code = $this->normalizeCode($code);

        if ($code === '') {
            return null;
        }

        return PlanCoupon::query()->where('code', $code)->first();
    }

    public function resolveForPlan(string $code, string $planKey): PlanCoupon
    {
        $coupon = $this->findByCode($code);

        if ($coupon === null) {
            throw new InvalidArgumentException('This coupon code is invalid.');
        }

        if (! $coupon->isValidNow()) {
            throw new InvalidArgumentException('This coupon code is inactive or has expired.');
        }

        if (! $coupon->appliesToPlan($planKey)) {
            throw new InvalidArgumentException('This coupon does not apply to the selected plan.');
        }

        return $coupon;
    }

    public function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}
