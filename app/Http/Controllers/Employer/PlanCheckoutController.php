<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\StorePlanChequeCheckoutRequest;
use App\Services\EmployerPlanCheckoutService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class PlanCheckoutController extends Controller
{
    public function __construct(
        protected EmployerPlanCheckoutService $checkoutService,
    ) {}

    public function quote(string $planKey): JsonResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $this->checkoutService->isChequeMode()) {
            return response()->json(['message' => 'Online checkout is not available yet.'], 422);
        }

        try {
            $this->checkoutService->assertCanPurchase($user, $planKey);
            $quote = $this->checkoutService->quote($planKey);
            $profile = $user->referrerProfile;

            return response()->json(array_merge($quote, [
                'company_name' => $profile->company_name,
                'payment_notice' => config('hirevo_plans.checkout.payment_notice'),
                'pending_message' => config('hirevo_plans.checkout.pending_message'),
            ]));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function storeCheque(StorePlanChequeCheckoutRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (! $this->checkoutService->isChequeMode()) {
            return response()->json(['message' => 'Online checkout is not available yet.'], 422);
        }

        try {
            $payment = $this->checkoutService->createNetBankingPayment(
                user: $user,
                planKey: $request->validated('plan_key'),
                utrReference: $request->validated('utr_reference'),
                paymentDate: $request->validated('payment_date'),
            );

            return response()->json([
                'message' => config('hirevo_plans.checkout.pending_message'),
                'payment_id' => $payment->id,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
