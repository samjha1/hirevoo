<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\EmployerPlanCheckoutService;
use App\Services\EmployerPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlansController extends Controller
{
    public function __construct(
        protected EmployerPlanService $planService,
        protected EmployerPlanCheckoutService $checkoutService,
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home');
        }

        $profile = $user->referrerProfile;
        $currentPlan = $this->planService->planKey($profile);
        $pendingPayment = $this->checkoutService->pendingPaymentForUser($user);

        return view('hirevo.employer.plans.index', [
            'plans' => $this->planService->allPlansKeyed(),
            'hero' => config('hirevo_plans.hero', []),
            'comparison' => config('hirevo_plans.comparison', []),
            'payPerHire' => config('hirevo_plans.pay_per_hire', []),
            'addons' => config('hirevo_plans.addons', []),
            'cta' => config('hirevo_plans.cta', []),
            'currentPlan' => $currentPlan,
            'credits' => $this->planService->jobPostingCredits($profile),
            'hasSubscription' => $this->planService->hasActiveSubscription($profile),
            'subscriptionStartedAt' => $profile?->subscription_started_at,
            'subscriptionExpiresAt' => $profile?->subscription_expires_at,
            'pendingPayment' => $pendingPayment,
            'isApproved' => (bool) ($profile?->is_approved),
            'employerCheckoutMode' => $this->checkoutService->uiCheckoutMode(),
            'razorpayKeyId' => config('razorpay.key_id'),
            'billingDurationOptions' => config('hirevo_plans.billing_duration_options', [1, 3, 6, 12]),
            'defaultBillingMonths' => (int) config('hirevo_plans.default_billing_months', 1),
        ]);
    }
}
