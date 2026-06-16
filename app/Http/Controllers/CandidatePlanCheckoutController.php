<?php

namespace App\Http\Controllers;

use App\Http\Requests\Candidate\CreateCandidateRazorpayOrderRequest;
use App\Http\Requests\Candidate\ScheduleCandidateRenewalPlanRequest;
use App\Http\Requests\Candidate\SyncCandidateRazorpayPaymentRequest;
use App\Http\Requests\Candidate\VerifyCandidateRazorpayPaymentRequest;
use App\Services\CandidatePlanCheckoutService;
use App\Services\CandidatePlanService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class CandidatePlanCheckoutController extends Controller
{
    public function __construct(
        protected CandidatePlanCheckoutService $checkout,
        protected CandidatePlanService $plans,
    ) {}

    public function quote(string $planKey): JsonResponse
    {
        $user = auth()->user();
        if (! $user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $this->checkout->assertCanPurchase($user, $planKey);

            return response()->json($this->checkout->quote($planKey));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function createOrder(CreateCandidateRazorpayOrderRequest $request): JsonResponse
    {
        $user = auth()->user();

        try {
            $order = $this->checkout->createRazorpayOrder($user, $request->validated('plan_key'));

            return response()->json($order);
        } catch (InvalidArgumentException $e) {
            $status = str_contains(strtolower($e->getMessage()), 'configured') ? 500 : 422;

            return response()->json(['message' => $e->getMessage()], $status);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to create payment order.'], 500);
        }
    }

    public function verifyPayment(VerifyCandidateRazorpayPaymentRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        try {
            $payment = $this->checkout->verifyAndComplete(
                user: $user,
                razorpayOrderId: $data['razorpay_order_id'],
                razorpayPaymentId: $data['razorpay_payment_id'],
                razorpaySignature: $data['razorpay_signature'],
            );

            return response()->json([
                'message' => config('hirevo_candidate_plans.checkout.success_message'),
                'payment_id' => $payment->id,
                'redirect' => route('candidate.dashboard'),
            ]);
        } catch (InvalidArgumentException $e) {
            $message = $e->getMessage();
            $status = str_contains(strtolower($message), 'signature') ? 400 : 422;

            return response()->json(['message' => $message], $status);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Payment verification failed.'], 500);
        }
    }

    public function syncPayment(SyncCandidateRazorpayPaymentRequest $request): JsonResponse
    {
        $user = auth()->user();

        try {
            $payment = $this->checkout->syncOrderFromGateway(
                $user,
                $request->validated('razorpay_order_id'),
            );

            return response()->json([
                'message' => config('hirevo_candidate_plans.checkout.success_message'),
                'payment_id' => $payment->id,
                'redirect' => route('candidate.dashboard'),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to sync payment status.'], 500);
        }
    }

    public function scheduleRenewalPlan(ScheduleCandidateRenewalPlanRequest $request): JsonResponse
    {
        $user = auth()->user();
        $planKey = $request->validated('plan_key');

        try {
            $profile = $this->plans->scheduleRenewalPlan($user, $planKey);
            $planConfig = $this->plans->planConfig($planKey);
            $planName = $planConfig['name'] ?? ucfirst($planKey);
            $expiresAt = $profile->premium_expires_at?->format('d M Y');

            return response()->json([
                'message' => $expiresAt
                    ? "You will switch to {$planName} when your current plan ends on {$expiresAt}."
                    : "You will switch to {$planName} at your next renewal.",
                'renewal_plan' => $planKey,
                'renewal_plan_name' => $planName,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to schedule plan switch.'], 500);
        }
    }

    public function clearRenewalPlan(): JsonResponse
    {
        $user = auth()->user();

        try {
            $this->plans->clearRenewalPlan($user);

            return response()->json(['message' => 'Scheduled plan switch cancelled.']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to cancel scheduled switch.'], 500);
        }
    }
}
