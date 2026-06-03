<?php

namespace App\Services;

use App\Mail\EmployerPlanAgreementMail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;

class EmployerPlanCheckoutService
{
    public const PAYMENT_TYPE = Payment::TYPE_EMPLOYER_SUBSCRIPTION;

    public function __construct(
        protected EmployerPlanService $planService,
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
        $plan = config("hirevo_plans.plans.{$planKey}");

        if ($plan === null || ! empty($plan['custom_price']) || $plan['price_inr'] === null) {
            throw new InvalidArgumentException('This plan cannot be purchased online.');
        }

        return array_merge($plan, ['key' => $planKey]);
    }

    /**
     * @return array<string, mixed>
     */
    public function quote(string $planKey): array
    {
        $plan = $this->resolvePurchasablePlan($planKey);
        $amounts = $this->calculateAmounts((float) $plan['price_inr']);

        return array_merge([
            'plan_key' => $plan['key'],
            'plan_name' => $plan['name'],
            'price_sub' => $plan['price_sub'] ?? '',
        ], $amounts);
    }

    /**
     * @return array<string, mixed>
     */
    public function calculateAmounts(float $baseAmount): array
    {
        $gstRate = (float) config('hirevo_plans.checkout.gst_rate', 18);
        $gstAmount = round($baseAmount * $gstRate / 100, 2);
        $totalAmount = round($baseAmount + $gstAmount, 2);

        return [
            'base_amount' => $baseAmount,
            'gst_rate' => $gstRate,
            'gst_amount' => $gstAmount,
            'total_amount' => $totalAmount,
            'currency' => 'INR',
        ];
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
            ->where('payment_gateway', Payment::GATEWAY_CHEQUE)
            ->where('created_at', '>=', now()->subDays(7))
            ->where('meta->plan_key', strtolower(trim($planKey)))
            ->exists();

        if ($hasPending) {
            throw new InvalidArgumentException('You already have a pending cheque payment for this plan. We will activate it after verification.');
        }
    }

    public function createChequePayment(
        User $user,
        string $planKey,
        string $chequeNumber,
        string $chequeDate,
    ): Payment {
        $this->assertCanPurchase($user, $planKey);

        $profile = $user->referrerProfile;
        $plan = $this->resolvePurchasablePlan($planKey);
        $amounts = $this->calculateAmounts((float) $plan['price_inr']);
        $acceptedAt = now()->toIso8601String();

        $payment = Payment::create([
            'user_id' => $user->id,
            'type' => self::PAYMENT_TYPE,
            'amount' => $amounts['total_amount'],
            'currency' => 'INR',
            'payment_gateway' => Payment::GATEWAY_CHEQUE,
            'payment_reference' => Str::limit(trim($chequeNumber), 191),
            'status' => Payment::STATUS_PENDING,
            'meta' => [
                'plan_key' => $plan['key'],
                'plan_name' => $plan['name'],
                'base_amount' => $amounts['base_amount'],
                'gst_rate' => $amounts['gst_rate'],
                'gst_amount' => $amounts['gst_amount'],
                'cheque_date' => $chequeDate,
                'company_name' => $profile->company_name,
                'company_email' => $profile->company_email,
                'agreement_accepted_at' => $acceptedAt,
                'billing_period' => 'monthly',
            ],
        ]);

        $email = strtolower(trim((string) $profile->company_email));
        if ($email !== '') {
            Mail::to($email)->send(new EmployerPlanAgreementMail(
                user: $user,
                profile: $profile,
                payment: $payment,
                plan: $plan,
                amounts: $amounts,
                chequeNumber: trim($chequeNumber),
                chequeDate: $chequeDate,
            ));
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

        $plan = $this->planService->planConfig($planKey);
        if (isset($plan['database_credits_included']) && is_numeric($plan['database_credits_included'])) {
            $credits = (int) $plan['database_credits_included'];
            if ($credits > 0) {
                $profile->increment('credits', $credits);
            }
        }

        return $payment->fresh();
    }
}
