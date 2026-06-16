<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\CreateEmployerRazorpayOrderRequest;
use App\Http\Requests\Employer\StorePlanChequeCheckoutRequest;
use App\Http\Requests\Employer\SyncEmployerRazorpayPaymentRequest;
use App\Http\Requests\Employer\VerifyEmployerRazorpayPaymentRequest;
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

        if ($this->checkoutService->checkoutMode() === null) {
            return response()->json(['message' => 'Online checkout is not available yet.'], 422);
        }

        try {
            $this->checkoutService->assertCanPurchase($user, $planKey, $this->billingMonthsFromRequest());
            $couponCode = request()->query('coupon_code');
            $quote = $this->checkoutService->quote(
                $planKey,
                is_string($couponCode) ? $couponCode : null,
                $this->billingMonthsFromRequest(),
            );
            $profile = $user->referrerProfile;

            return response()->json(array_merge($quote, [
                'company_name' => $profile->company_name,
                'payment_notice' => $this->checkoutService->isRazorpayMode()
                    ? 'Pay securely via Razorpay — card, UPI, or net banking. Your plan activates immediately after payment.'
                    : config('hirevo_plans.checkout.payment_notice'),
                'pending_message' => config('hirevo_plans.checkout.pending_message'),
                'checkout_mode' => $this->checkoutService->checkoutMode(),
            ]));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function createOrder(CreateEmployerRazorpayOrderRequest $request): JsonResponse
    {
        $user = auth()->user();

        try {
            $order = $this->checkoutService->createRazorpayOrder(
                $user,
                $request->validated('plan_key'),
                $request->validated('coupon_code'),
                isset($request->validated()['billing_months'])
                    ? (int) $request->validated('billing_months')
                    : null,
            );

            return response()->json($order);
        } catch (InvalidArgumentException $e) {
            $status = str_contains(strtolower($e->getMessage()), 'configured') ? 500 : 422;

            return response()->json(['message' => $e->getMessage()], $status);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to create payment order.'], 500);
        }
    }

    public function verifyPayment(VerifyEmployerRazorpayPaymentRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        try {
            $payment = $this->checkoutService->verifyAndComplete(
                user: $user,
                razorpayOrderId: $data['razorpay_order_id'],
                razorpayPaymentId: $data['razorpay_payment_id'],
                razorpaySignature: $data['razorpay_signature'],
            );

            return response()->json([
                'message' => config('hirevo_plans.checkout.success_message', 'Payment successful! Your plan is now active.'),
                'payment_id' => $payment->id,
                'redirect' => route('employer.dashboard'),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Payment verification failed.'], 500);
        }
    }

    public function syncPayment(SyncEmployerRazorpayPaymentRequest $request): JsonResponse
    {
        $user = auth()->user();

        try {
            $payment = $this->checkoutService->syncOrderFromGateway(
                $user,
                $request->validated('razorpay_order_id'),
            );

            return response()->json([
                'message' => config('hirevo_plans.checkout.success_message', 'Payment successful! Your plan is now active.'),
                'payment_id' => $payment->id,
                'redirect' => route('employer.dashboard'),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to sync payment status.'], 500);
        }
    }

    public function storeCheque(StorePlanChequeCheckoutRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (! $this->checkoutService->isChequeMode()) {
            return response()->json(['message' => 'Cheque checkout is not available.'], 422);
        }

        try {
            $payment = $this->checkoutService->createNetBankingPayment(
                user: $user,
                planKey: $request->validated('plan_key'),
                utrReference: $request->validated('utr_reference'),
                paymentDate: $request->validated('payment_date'),
                couponCode: $request->validated('coupon_code'),
                billingMonths: isset($request->validated()['billing_months'])
                    ? (int) $request->validated('billing_months')
                    : null,
            );

            return response()->json([
                'message' => config('hirevo_plans.checkout.pending_message'),
                'payment_id' => $payment->id,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    protected function billingMonthsFromRequest(): ?int
    {
        $value = request()->query('billing_months', request()->input('billing_months'));

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
