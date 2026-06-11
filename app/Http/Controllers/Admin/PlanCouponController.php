<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployerPlan;
use App\Models\PlanCoupon;
use App\Services\PlanCouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanCouponController extends Controller
{
    public function __construct(
        protected PlanCouponService $couponService,
    ) {}

    public function index(): View
    {
        return view('hirevo.admin.plan-coupons.index', [
            'coupons' => PlanCoupon::query()->orderByDesc('created_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('hirevo.admin.plan-coupons.form', [
            'coupon' => new PlanCoupon([
                'is_active' => true,
                'discount_percent' => 10,
            ]),
            'plans' => $this->planOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        PlanCoupon::query()->create($data);

        return redirect()
            ->route('admin.plan-coupons.index')
            ->with('success', 'Coupon code created.');
    }

    public function edit(PlanCoupon $planCoupon): View
    {
        return view('hirevo.admin.plan-coupons.form', [
            'coupon' => $planCoupon,
            'plans' => $this->planOptions(),
        ]);
    }

    public function update(Request $request, PlanCoupon $planCoupon): RedirectResponse
    {
        $data = $this->validated($request, $planCoupon);
        $planCoupon->update($data);

        return redirect()
            ->route('admin.plan-coupons.index')
            ->with('success', 'Coupon code updated.');
    }

    public function destroy(PlanCoupon $planCoupon): RedirectResponse
    {
        $planCoupon->delete();

        return redirect()
            ->route('admin.plan-coupons.index')
            ->with('success', 'Coupon code deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?PlanCoupon $coupon = null): array
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('plan_coupons', 'code')->ignore($coupon?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'discount_percent' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'applicable_plan_slugs' => ['nullable', 'array'],
            'applicable_plan_slugs.*' => ['string', Rule::exists('employer_plans', 'slug')],
        ]);

        $data['code'] = $this->couponService->normalizeCode($data['code']);
        $data['is_active'] = $request->boolean('is_active');
        $data['applicable_plan_slugs'] = $this->normalizePlanSlugs($data['applicable_plan_slugs'] ?? null);

        return $data;
    }

    /**
     * @return array<int, string>
     */
    protected function planOptions(): array
    {
        return EmployerPlan::query()
            ->active()
            ->ordered()
            ->pluck('name', 'slug')
            ->all();
    }

    /**
     * @param  array<int, string>|null  $slugs
     * @return array<int, string>|null
     */
    protected function normalizePlanSlugs(?array $slugs): ?array
    {
        if ($slugs === null || $slugs === []) {
            return null;
        }

        return array_values(array_unique(array_map(
            fn (string $slug) => strtolower(trim($slug)),
            $slugs
        )));
    }
}
