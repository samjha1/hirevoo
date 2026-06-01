<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\EmployerPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlansController extends Controller
{
    public function __construct(
        protected EmployerPlanService $planService
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home');
        }

        $profile = $user->referrerProfile;
        $currentPlan = $this->planService->planKey($profile);

        return view('hirevo.employer.plans.index', [
            'plans' => config('hirevo_plans.plans', []),
            'hero' => config('hirevo_plans.hero', []),
            'comparison' => config('hirevo_plans.comparison', []),
            'payPerHire' => config('hirevo_plans.pay_per_hire', []),
            'addons' => config('hirevo_plans.addons', []),
            'cta' => config('hirevo_plans.cta', []),
            'currentPlan' => $currentPlan,
            'credits' => $this->planService->jobPostingCredits($profile),
            'hasSubscription' => $this->planService->hasActiveSubscription($profile),
        ]);
    }
}
