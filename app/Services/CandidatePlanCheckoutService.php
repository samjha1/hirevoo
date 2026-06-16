<?php



namespace App\Services;



use App\Models\CandidateProfile;

use App\Models\Payment;

use App\Models\User;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;

use InvalidArgumentException;



class CandidatePlanCheckoutService

{

    public const PAYMENT_TYPE = Payment::TYPE_PREMIUM_SUBSCRIPTION;



    public function __construct(

        protected RazorpayService $razorpay,

        protected CandidatePremiumService $premium,

        protected CandidatePlanService $plans,

    ) {}



    /**

     * @return array<string, mixed>

     */

    public function resolvePlan(string $planKey): array

    {

        $planKey = strtolower(trim($planKey));

        $config = $this->plans->planConfig($planKey);



        if ($config === null || empty($config['price_inr'])) {

            throw new InvalidArgumentException('Invalid plan selected.');

        }



        return $config;

    }



    /**

     * @return array<string, mixed>

     */

    public function quote(string $planKey): array

    {

        $plan = $this->resolvePlan($planKey);

        $amounts = $this->calculateAmounts((float) $plan['price_inr']);



        return array_merge([

            'plan_key' => $plan['key'],

            'plan_name' => $plan['name'],

            'tagline' => $plan['tagline'] ?? '',

            'duration_days' => $plan['duration_days'] ?? 30,

        ], $amounts);

    }



    /**

     * @return array<string, mixed>

     */

    public function calculateAmounts(float $listPrice): array

    {

        $gstRate = (float) config('hirevo_candidate_plans.checkout.gst_rate', 18);

        $baseAmount = round($listPrice, 2);

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

        if (! $user->isCandidate()) {

            throw new InvalidArgumentException('Only candidate accounts can purchase this plan.');

        }



        $this->resolvePlan($planKey);



        if ($this->premium->hasAccess($user)) {

            throw new InvalidArgumentException('You already have an active premium plan.');

        }



        if (! $this->razorpay->isConfigured()) {

            throw new InvalidArgumentException('Online payment is not available right now. Please try again later.');

        }

    }



    /**

     * @return array{order_id: string, amount: int, currency: string, key_id: string, payment_id: int}

     */

    public function createRazorpayOrder(User $user, string $planKey): array

    {

        $this->assertCanPurchase($user, $planKey);



        $plan = $this->resolvePlan($planKey);

        $amounts = $this->calculateAmounts((float) $plan['price_inr']);

        $amountPaise = (int) round($amounts['total_amount'] * 100);



        if ($amountPaise < 100) {

            throw new InvalidArgumentException('Order amount is too low.');

        }



        $receipt = Str::limit('cand_'.$user->id.'_'.time(), 40, '');



        $payment = Payment::create([

            'user_id' => $user->id,

            'type' => self::PAYMENT_TYPE,

            'amount' => $amounts['total_amount'],

            'currency' => 'INR',

            'payment_gateway' => Payment::GATEWAY_RAZORPAY,

            'status' => Payment::STATUS_PENDING,

            'meta' => [

                'plan_key' => $plan['key'],

                'plan_name' => $plan['name'],

                'base_amount' => $amounts['base_amount'],

                'gst_rate' => $amounts['gst_rate'],

                'gst_amount' => $amounts['gst_amount'],

                'duration_days' => $plan['duration_days'] ?? 30,

                'referral_requests_limit' => $plan['referral_requests_limit'] ?? 3,

            ],

        ]);



        $order = $this->razorpay->createOrder(

            amountPaise: $amountPaise,

            currency: 'INR',

            receipt: $receipt,

            notes: [

                'payment_id' => (string) $payment->id,

                'user_id' => (string) $user->id,

                'plan_key' => $plan['key'],

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



        $payment = $this->findPendingPayment($user, $razorpayOrderId);



        return $this->completeFromGateway($payment, $razorpayPaymentId, $razorpaySignature);

    }



    public function syncOrderFromGateway(User $user, string $razorpayOrderId, ?string $expectedPaymentId = null): Payment

    {

        $payment = $this->findPendingPayment($user, $razorpayOrderId);



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



        return DB::transaction(function () use ($payment, $razorpayPaymentId, $verificationSource) {

            $payment->update([

                'status' => Payment::STATUS_COMPLETED,

                'meta' => array_merge($payment->meta ?? [], [

                    'razorpay_payment_id' => $razorpayPaymentId,

                    'verification_source' => $verificationSource,

                    'completed_at' => now()->toIso8601String(),

                ]),

            ]);



            $this->activatePremium($payment->user, $payment->fresh());

            $this->plans->clearPlanCache();



            Log::info('Candidate premium activated via Razorpay', [

                'payment_id' => $payment->id,

                'user_id' => $payment->user_id,

                'plan_key' => $payment->meta['plan_key'] ?? null,

                'verification_source' => $verificationSource,

            ]);



            return $payment->fresh();

        });

    }



    protected function findPendingPayment(User $user, string $razorpayOrderId): Payment

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



    protected function activatePremium(User $user, Payment $payment): void

    {

        $meta = $payment->meta ?? [];

        $planKey = (string) ($meta['plan_key'] ?? '');

        $durationDays = max(1, (int) ($meta['duration_days'] ?? 30));

        $referralLimit = max(1, (int) ($meta['referral_requests_limit'] ?? 3));



        $profile = $user->candidateProfile ?? CandidateProfile::query()->firstOrCreate(

            ['user_id' => $user->id],

            ['referral_requests_limit' => 3],

        );



        $expiresAt = $profile->premium_expires_at && $profile->premium_expires_at->isFuture()

            ? $profile->premium_expires_at->copy()->addDays($durationDays)

            : now()->addDays($durationDays);



        $profile->update([

            'is_premium' => true,

            'subscription_plan' => $planKey !== '' ? $planKey : $profile->subscription_plan,

            'subscription_started_at' => $profile->subscription_started_at ?? now(),

            'premium_expires_at' => $expiresAt,

            'referral_requests_limit' => max((int) $profile->referral_requests_limit, $referralLimit),

        ]);

    }

}


