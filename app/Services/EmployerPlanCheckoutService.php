<?php

namespace App\Services;

use App\Mail\EmployerPlanAgreementMail;
use App\Models\EmployerPlan;
use App\Models\Payment;
use App\Models\PlanCoupon;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;

class EmployerPlanCheckoutService
{
    public const PAYMENT_TYPE = Payment::TYPE_EMPLOYER_SUBSCRIPTION;

    public function __construct(
        protected EmployerPlanService $planService,
        protected PlanCouponService $couponService,
    ) {}

    public function isChequeMode(): bool
    {
        return config('hirevo_plans.checkout.mode', 'cheque') === 'cheque';
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvePurchasablePlan(string $planKey): array
    {
        $planKey = strtolower(trim($planKey));
        $plan = $this->planService->findPlan($planKey);

        if ($plan !== null) {
            if (! $plan->isPurchasable()) {
                throw new InvalidArgumentException('This plan cannot be purchased online.');
            }

            return array_merge($plan->toDisplayArray(), ['key' => $plan->slug]);
        }

        $legacy = config("hirevo_plans.plans.{$planKey}");
        if ($legacy === null || ! empty($legacy['custom_price']) || $legacy['price_inr'] === null) {
            throw new InvalidArgumentException('This plan cannot be purchased online.');
        }

        return array_merge($legacy, ['key' => $planKey]);
    }

    /**
     * @return array<string, mixed>
     */
    public function quote(string $planKey, ?string $couponCode = null): array
    {
        $plan = $this->resolvePurchasablePlan($planKey);
        $coupon = $this->resolveOptionalCoupon($couponCode, $plan['key']);
        $amounts = $this->calculateAmounts((float) $plan['price_inr'], $coupon);

        return array_merge([
            'plan_key' => $plan['key'],
            'plan_name' => $plan['name'],
            'price_sub' => $plan['price_sub'] ?? '',
            'job_credits_included' => $plan['job_credits_included'] ?? $plan['database_credits_included'] ?? 0,
        ], $amounts);
    }

    /**
     * @return array<string, mixed>
     */
    public function calculateAmounts(float $listPrice, ?PlanCoupon $coupon = null): array
    {
        $gstRate = (float) config('hirevo_plans.checkout.gst_rate', 18);
        $originalBase = round($listPrice, 2);
        $discountPercent = $coupon !== null ? (float) $coupon->discount_percent : 0.0;
        $discountAmount = $coupon !== null
            ? round($originalBase * $discountPercent / 100, 2)
            : 0.0;
        $baseAmount = round(max(0, $originalBase - $discountAmount), 2);
        $gstAmount = round($baseAmount * $gstRate / 100, 2);
        $totalAmount = round($baseAmount + $gstAmount, 2);

        return [
            'original_base_amount' => $originalBase,
            'base_amount' => $baseAmount,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'gst_rate' => $gstRate,
            'gst_amount' => $gstAmount,
            'total_amount' => $totalAmount,
            'currency' => 'INR',
            'coupon_code' => $coupon?->code,
            'coupon_applied' => $coupon !== null,
        ];
    }

    protected function resolveOptionalCoupon(?string $couponCode, string $planKey): ?PlanCoupon
    {
        if ($couponCode === null || trim($couponCode) === '') {
            return null;
        }

        return $this->couponService->resolveForPlan($couponCode, $planKey);
    }

    public function assertCanPurchase(User $user, string $planKey): void
    {
        $profile = $user->referrerProfile;
        if ($profile === null) {
            throw new InvalidArgumentException('Complete your employer profile before purchasing a plan.');
        }

        if (trim((string) $profile->company_name) === '') {
            throw new InvalidArgumentException('Add your company name in profile before purchasing a plan.');
        }

        if (! $profile->is_approved) {
            throw new InvalidArgumentException('Your employer account must be approved before purchasing a plan.');
        }

        $this->resolvePurchasablePlan($planKey);

        if ($this->planService->hasActiveSubscription($profile)) {
            $currentKey = $this->planService->planKey($profile);
            $currentRank = $this->planService->planPriceRank($currentKey);
            $newRank = $this->planService->planPriceRank($planKey);

            if ($newRank <= $currentRank) {
                throw new InvalidArgumentException('You already have an active subscription on this plan or a higher tier.');
            }
        }

        $hasPending = Payment::query()
            ->where('user_id', $user->id)
            ->where('type', self::PAYMENT_TYPE)
            ->where('status', Payment::STATUS_PENDING)
            ->whereIn('payment_gateway', [Payment::GATEWAY_CHEQUE, Payment::GATEWAY_NETBANKING])
            ->where('created_at', '>=', now()->subDays(7))
            ->where('meta->plan_key', strtolower(trim($planKey)))
            ->exists();

        if ($hasPending) {
            throw new InvalidArgumentException('You already have a pending payment for this plan. We will activate it after verification.');
        }
    }

    public function createChequePayment(
        User $user,
        string $planKey,
        string $chequeNumber,
        string $chequeDate,
    ): Payment {
        return $this->createOfflinePayment(
            user: $user,
            planKey: $planKey,
            gateway: Payment::GATEWAY_CHEQUE,
            paymentReference: $chequeNumber,
            paymentDate: $chequeDate,
            paymentDateMetaKey: 'cheque_date',
        );
    }

    public function createNetBankingPayment(
        User $user,
        string $planKey,
        string $utrReference,
        string $paymentDate,
        ?string $couponCode = null,
    ): Payment {
        return $this->createOfflinePayment(
            user: $user,
            planKey: $planKey,
            gateway: Payment::GATEWAY_NETBANKING,
            paymentReference: $utrReference,
            paymentDate: $paymentDate,
            paymentDateMetaKey: 'payment_date',
            couponCode: $couponCode,
        );
    }

    protected function createOfflinePayment(
        User $user,
        string $planKey,
        string $gateway,
        string $paymentReference,
        string $paymentDate,
        string $paymentDateMetaKey,
        ?string $couponCode = null,
    ): Payment {
        $this->assertCanPurchase($user, $planKey);

        $profile = $user->referrerProfile;
        $plan = $this->resolvePurchasablePlan($planKey);
        $coupon = $this->resolveOptionalCoupon($couponCode, $plan['key']);
        $amounts = $this->calculateAmounts((float) $plan['price_inr'], $coupon);
        $acceptedAt = now()->toIso8601String();
        $reference = Str::limit(trim($paymentReference), 191);

        $meta = [
            'plan_key' => $plan['key'],
            'plan_name' => $plan['name'],
            'original_base_amount' => $amounts['original_base_amount'],
            'base_amount' => $amounts['base_amount'],
            'discount_percent' => $amounts['discount_percent'],
            'discount_amount' => $amounts['discount_amount'],
            'gst_rate' => $amounts['gst_rate'],
            'gst_amount' => $amounts['gst_amount'],
            'job_credits_included' => $plan['job_credits_included'] ?? $plan['database_credits_included'] ?? 0,
            $paymentDateMetaKey => $paymentDate,
            'company_name' => $profile->company_name,
            'company_email' => $profile->company_email,
            'agreement_accepted_at' => $acceptedAt,
            'billing_period' => $plan['billing_period'] ?? 'monthly',
        ];

        if ($coupon !== null) {
            $meta['coupon_code'] = $coupon->code;
            $meta['coupon_id'] = $coupon->id;
        }

        if ($gateway === Payment::GATEWAY_NETBANKING) {
            $meta['bank_account'] = config('hirevo_plans.checkout.bank_account', []);
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'type' => self::PAYMENT_TYPE,
            'amount' => $amounts['total_amount'],
            'currency' => 'INR',
            'payment_gateway' => $gateway,
            'payment_reference' => $reference,
            'status' => Payment::STATUS_PENDING,
            'meta' => $meta,
        ]);

        if ($coupon !== null) {
            $coupon->incrementUsage();
        }

        $email = strtolower(trim((string) $profile->company_email));
        if ($email !== '') {
            try {
                Mail::to($email)->send(new EmployerPlanAgreementMail(
                    user: $user,
                    profile: $profile,
                    payment: $payment,
                    plan: $plan,
                    amounts: $amounts,
                    chequeNumber: $reference,
                    chequeDate: $paymentDate,
                ));
            } catch (\Throwable $e) {
                Log::warning('Employer plan agreement email failed', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $payment;
    }

    public function completePayment(Payment $payment): Payment
    {
        if ($payment->status === Payment::STATUS_COMPLETED) {
            return $payment;
        }

        if ($payment->type !== self::PAYMENT_TYPE) {
            throw new InvalidArgumentException('Payment is not an employer subscription.');
        }

        $planKey = (string) ($payment->meta['plan_key'] ?? '');
        if ($planKey === '') {
            throw new InvalidArgumentException('Payment is missing plan_key in meta.');
        }

        $user = $payment->user;
        $profile = $user?->referrerProfile;

        if ($profile === null) {
            throw new InvalidArgumentException('Employer profile not found for this payment.');
        }

        $payment->update(['status' => Payment::STATUS_COMPLETED]);

        $this->planService->activateSubscription($profile, $planKey);
        $creditsGranted = $this->planService->grantPlanJobCredits($profile->fresh(), $planKey);

        Log::info('Employer subscription activated', [
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'plan_key' => $planKey,
            'job_credits_granted' => $creditsGranted,
            'subscription_expires_at' => $profile->fresh()->subscription_expires_at?->toIso8601String(),
        ]);

        return $payment->fresh();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Payment>
     */
    public function pendingPayments()
    {
        return Payment::query()
            ->with(['user.referrerProfile'])
            ->where('type', self::PAYMENT_TYPE)
            ->where('status', Payment::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get();
    }

    public function pendingPaymentForUser(User $user): ?Payment
    {
        return Payment::query()
            ->where('user_id', $user->id)
            ->where('type', self::PAYMENT_TYPE)
            ->where('status', Payment::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->first();
    }
}
