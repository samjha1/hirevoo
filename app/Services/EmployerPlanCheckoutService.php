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
        protected RazorpayService $razorpay,
    ) {}

    public function isChequeMode(): bool
    {
        return config('hirevo_plans.checkout.mode', 'razorpay') === 'cheque';
    }

    public function isRazorpayMode(): bool
    {
        if ($this->isChequeMode()) {
            return false;
        }

        return $this->razorpay->isConfigured();
    }

    public function checkoutMode(): ?string
    {
        if ($this->isRazorpayMode()) {
            return 'razorpay';
        }

        if ($this->isChequeMode()) {
            return 'cheque';
        }

        return null;
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
    public function quote(string $planKey, ?string $couponCode = null, ?int $billingMonths = null): array
    {
        $plan = $this->resolvePurchasablePlan($planKey);
        $months = $this->planService->resolveBillingMonths($billingMonths, $plan);
        $coupon = $this->resolveOptionalCoupon($couponCode, $plan['key']);
        $listPrice = $this->listPriceForMonths($plan, $months);
        $amounts = $this->calculateAmounts($listPrice, $coupon);

        return array_merge([
            'plan_key' => $plan['key'],
            'plan_name' => $plan['name'],
            'price_sub' => $this->priceSubLabel($plan, $months),
            'billing_months' => $months,
            'billing_duration_options' => $this->planService->billingDurationOptions($plan),
            'monthly_price_inr' => (int) ($plan['price_inr'] ?? 0),
            'job_credits_included' => $plan['job_credits_included'] ?? $plan['database_credits_included'] ?? 0,
        ], $amounts);
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    protected function listPriceForMonths(array $plan, int $billingMonths): float
    {
        if ($this->planService->isLaunchPlan($plan)) {
            return (float) $plan['price_inr'];
        }

        return (float) $plan['price_inr'] * $billingMonths;
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    protected function priceSubLabel(array $plan, int $billingMonths): string
    {
        if ($this->planService->isLaunchPlan($plan)) {
            return (string) ($plan['price_sub'] ?? 'one-time');
        }

        $monthly = number_format((int) $plan['price_inr']);
        if ($billingMonths === 1) {
            return "₹{$monthly} per month, billed monthly";
        }

        $total = number_format((int) $plan['price_inr'] * $billingMonths);

        return "₹{$monthly}/mo × {$billingMonths} months = ₹{$total} upfront";
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

    public function assertCanPurchase(User $user, string $planKey, ?int $billingMonths = null): void
    {
        $profile = $user->referrerProfile;
        if ($profile === null) {
            throw new InvalidArgumentException('Complete your employer profile before purchasing a plan.');
        }

        if (trim((string) $profile->company_name) === '') {
            throw new InvalidArgumentException('Add your company name in profile before purchasing a plan.');
        }

        if (! $this->isRazorpayMode() && ! $profile->is_approved) {
            throw new InvalidArgumentException('Your employer account must be approved before purchasing a plan.');
        }

        $plan = $this->resolvePurchasablePlan($planKey);
        $months = $this->planService->resolveBillingMonths($billingMonths, $plan);

        if ($this->planService->hasActiveSubscription($profile)) {
            $currentKey = $this->planService->planKey($profile);
            $currentRank = $this->planService->planPriceRank($currentKey);
            $newRank = $this->planService->planPriceRank($planKey);

            if ($newRank <= $currentRank) {
                throw new InvalidArgumentException('You already have an active subscription on this plan or a higher tier.');
            }
        }

        $this->reconcileBlockingPendingPayments($user, $planKey, $months);

        $pending = $this->matchingPendingPayment($user, $planKey, $months);

        if ($pending !== null) {
            if ($pending->payment_gateway === Payment::GATEWAY_RAZORPAY) {
                throw new InvalidArgumentException('Your previous checkout is still processing. Wait a moment, refresh this page, and try again.');
            }

            throw new InvalidArgumentException('You already have a pending payment for this plan. We will activate it after verification.');
        }
    }

    public function reconcileBlockingPendingPayments(User $user, string $planKey, int $months): void
    {
        $pendings = $this->matchingPendingPaymentQuery($user, $planKey, $months)->get();

        foreach ($pendings as $payment) {
            if ($payment->payment_gateway === Payment::GATEWAY_RAZORPAY) {
                $this->reconcileRazorpayPendingPayment($payment);
            }
        }
    }

    protected function reconcileRazorpayPendingPayment(Payment $payment): void
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return;
        }

        $orderId = (string) ($payment->payment_reference ?: ($payment->meta['razorpay_order_id'] ?? ''));
        if ($orderId === '') {
            return;
        }

        $successful = $this->razorpay->findSuccessfulPaymentForOrder($orderId);
        if ($successful !== null) {
            $this->completeFromGateway($payment, $successful['id'], 'reconcile');

            return;
        }

        $failed = $this->razorpay->findLatestFailedPaymentForOrder($orderId);
        if ($failed !== null) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'meta' => array_merge($payment->meta ?? [], [
                    'razorpay_payment_id' => $failed['id'],
                    'gateway_error' => $failed['error_description'],
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);

            return;
        }

        $gatewayPayments = $this->razorpay->paymentsForOrder($orderId);
        if ($gatewayPayments === []) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'meta' => array_merge($payment->meta ?? [], [
                    'abandoned' => true,
                    'released_for_retry' => true,
                    'failed_at' => now()->toIso8601String(),
                    'gateway_error' => 'Checkout was not completed.',
                ]),
            ]);

            return;
        }

        $staleMinutes = max(5, (int) config('hirevo_plans.checkout.stale_razorpay_minutes', 60));
        if ($payment->created_at->lte(now()->subMinutes($staleMinutes))) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'meta' => array_merge($payment->meta ?? [], [
                    'stale' => true,
                    'failed_at' => now()->toIso8601String(),
                    'gateway_error' => 'Checkout expired. Please try again.',
                ]),
            ]);
        }
    }

    protected function matchingPendingPayment(User $user, string $planKey, int $months): ?Payment
    {
        return $this->matchingPendingPaymentQuery($user, $planKey, $months)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Payment>
     */
    protected function matchingPendingPaymentQuery(User $user, string $planKey, int $months)
    {
        return Payment::query()
            ->where('user_id', $user->id)
            ->where('type', self::PAYMENT_TYPE)
            ->where('status', Payment::STATUS_PENDING)
            ->whereIn('payment_gateway', [Payment::GATEWAY_CHEQUE, Payment::GATEWAY_NETBANKING, Payment::GATEWAY_RAZORPAY])
            ->where('created_at', '>=', now()->subDays(7))
            ->where('meta->plan_key', strtolower(trim($planKey)))
            ->where(function ($query) use ($months) {
                $query->where('meta->billing_months', $months);
                if ($months === 1) {
                    $query->orWhereNull('meta->billing_months');
                }
            });
    }

    public function createChequePayment(
        User $user,
        string $planKey,
        string $chequeNumber,
        string $chequeDate,
        ?int $billingMonths = null,
    ): Payment {
        return $this->createOfflinePayment(
            user: $user,
            planKey: $planKey,
            gateway: Payment::GATEWAY_CHEQUE,
            paymentReference: $chequeNumber,
            paymentDate: $chequeDate,
            paymentDateMetaKey: 'cheque_date',
            billingMonths: $billingMonths,
        );
    }

    public function createNetBankingPayment(
        User $user,
        string $planKey,
        string $utrReference,
        string $paymentDate,
        ?string $couponCode = null,
        ?int $billingMonths = null,
    ): Payment {
        return $this->createOfflinePayment(
            user: $user,
            planKey: $planKey,
            gateway: Payment::GATEWAY_NETBANKING,
            paymentReference: $utrReference,
            paymentDate: $paymentDate,
            paymentDateMetaKey: 'payment_date',
            couponCode: $couponCode,
            billingMonths: $billingMonths,
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
        ?int $billingMonths = null,
    ): Payment {
        $this->assertCanPurchase($user, $planKey, $billingMonths);

        $profile = $user->referrerProfile;
        $plan = $this->resolvePurchasablePlan($planKey);
        $months = $this->planService->resolveBillingMonths($billingMonths, $plan);
        $coupon = $this->resolveOptionalCoupon($couponCode, $plan['key']);
        $amounts = $this->calculateAmounts($this->listPriceForMonths($plan, $months), $coupon);
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
            'billing_months' => $months,
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

    /**
     * @return array{order_id: string, amount: int, currency: string, key_id: string, payment_id: int}
     */
    public function createRazorpayOrder(User $user, string $planKey, ?string $couponCode = null, ?int $billingMonths = null): array
    {
        if (! $this->isRazorpayMode()) {
            throw new InvalidArgumentException('Online payment is not available right now.');
        }

        $this->assertCanPurchase($user, $planKey, $billingMonths);

        $profile = $user->referrerProfile;
        $plan = $this->resolvePurchasablePlan($planKey);
        $months = $this->planService->resolveBillingMonths($billingMonths, $plan);
        $coupon = $this->resolveOptionalCoupon($couponCode, $plan['key']);
        $amounts = $this->calculateAmounts($this->listPriceForMonths($plan, $months), $coupon);
        $amountPaise = (int) round($amounts['total_amount'] * 100);

        if ($amountPaise < 100) {
            throw new InvalidArgumentException('Order amount is too low.');
        }

        $receipt = Str::limit('empl_'.$user->id.'_'.time(), 40, '');

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
            'company_name' => $profile->company_name,
            'company_email' => $profile->company_email,
            'billing_period' => $plan['billing_period'] ?? 'monthly',
            'billing_months' => $months,
        ];

        if ($coupon !== null) {
            $meta['coupon_code'] = $coupon->code;
            $meta['coupon_id'] = $coupon->id;
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'type' => self::PAYMENT_TYPE,
            'amount' => $amounts['total_amount'],
            'currency' => 'INR',
            'payment_gateway' => Payment::GATEWAY_RAZORPAY,
            'status' => Payment::STATUS_PENDING,
            'meta' => $meta,
        ]);

        if ($coupon !== null) {
            $coupon->incrementUsage();
        }

        $order = $this->razorpay->createOrder(
            amountPaise: $amountPaise,
            currency: 'INR',
            receipt: $receipt,
            notes: [
                'payment_id' => (string) $payment->id,
                'user_id' => (string) $user->id,
                'plan_key' => $plan['key'],
                'payment_type' => self::PAYMENT_TYPE,
            ],
        );

        $payment->update([
            'payment_reference' => $order['order_id'],
            'meta' => array_merge($payment->meta ?? [], [
                'razorpay_order_id' => $order['order_id'],
            ]),
        ]);

        return [
            'order_id' => $order['order_id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'key_id' => (string) config('razorpay.key_id'),
            'payment_id' => $payment->id,
        ];
    }

    public function verifyAndComplete(
        User $user,
        string $razorpayOrderId,
        string $razorpayPaymentId,
        string $razorpaySignature,
    ): Payment {
        if (! $this->razorpay->verifyCheckoutSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)) {
            return $this->syncOrderFromGateway($user, $razorpayOrderId, $razorpayPaymentId);
        }

        $payment = $this->findPendingRazorpayPayment($user, $razorpayOrderId);

        return $this->completeFromGateway($payment, $razorpayPaymentId, $razorpaySignature);
    }

    public function syncOrderFromGateway(User $user, string $razorpayOrderId, ?string $expectedPaymentId = null): Payment
    {
        $payment = $this->findPendingRazorpayPayment($user, $razorpayOrderId);

        if ($payment->status === Payment::STATUS_COMPLETED) {
            return $payment;
        }

        $successful = $this->razorpay->findSuccessfulPaymentForOrder($razorpayOrderId);

        if ($successful === null) {
            $failed = $this->razorpay->findLatestFailedPaymentForOrder($razorpayOrderId);
            if ($failed !== null) {
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'meta' => array_merge($payment->meta ?? [], [
                        'razorpay_payment_id' => $failed['id'],
                        'gateway_error' => $failed['error_description'],
                        'failed_at' => now()->toIso8601String(),
                    ]),
                ]);

                throw new InvalidArgumentException($failed['error_description']);
            }

            throw new InvalidArgumentException('Payment is not completed yet. If you were charged, wait a moment and refresh this page.');
        }

        if ($expectedPaymentId !== null && $successful['id'] !== $expectedPaymentId) {
            throw new InvalidArgumentException('Payment verification mismatch. Please contact support.');
        }

        return $this->completeFromGateway($payment, $successful['id'], 'gateway-sync');
    }

    public function completeFromGateway(Payment $payment, string $razorpayPaymentId, string $verificationSource): Payment
    {
        if ($payment->status === Payment::STATUS_COMPLETED) {
            return $payment;
        }

        $payment->update([
            'meta' => array_merge($payment->meta ?? [], [
                'razorpay_payment_id' => $razorpayPaymentId,
                'verification_source' => $verificationSource,
                'completed_at' => now()->toIso8601String(),
            ]),
        ]);

        return $this->completePayment($payment->fresh());
    }

    protected function findPendingRazorpayPayment(User $user, string $razorpayOrderId): Payment
    {
        $payment = Payment::query()
            ->where('user_id', $user->id)
            ->where('type', self::PAYMENT_TYPE)
            ->where('payment_gateway', Payment::GATEWAY_RAZORPAY)
            ->where('payment_reference', $razorpayOrderId)
            ->first();

        if ($payment === null) {
            throw new InvalidArgumentException('Payment record not found for this order.');
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

        $billingMonths = isset($payment->meta['billing_months'])
            ? (int) $payment->meta['billing_months']
            : null;

        $this->planService->activateSubscription($profile, $planKey, billingMonths: $billingMonths);
        $creditsGranted = $this->planService->grantPlanJobCredits($profile->fresh(), $planKey);
        $tokensGranted = $this->planService->grantPlanTalentPoolTokens($profile->fresh(), $planKey);

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
