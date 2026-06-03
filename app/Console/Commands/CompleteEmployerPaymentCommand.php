<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\EmployerPlanCheckoutService;
use Illuminate\Console\Command;

class CompleteEmployerPaymentCommand extends Command
{
    protected $signature = 'hirevo:complete-payment {payment_id : The payments table ID to mark completed}';

    protected $description = 'Mark an employer subscription payment as completed and activate the plan';

    public function handle(EmployerPlanCheckoutService $checkout): int
    {
        $paymentId = (int) $this->argument('payment_id');
        $payment = Payment::query()->find($paymentId);

        if ($payment === null) {
            $this->error("Payment #{$paymentId} not found.");

            return self::FAILURE;
        }

        try {
            $checkout->completePayment($payment);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $planKey = (string) ($payment->fresh()->meta['plan_key'] ?? '');
        $this->info("Payment #{$paymentId} completed. Subscription plan \"{$planKey}\" activated for user #{$payment->user_id}.");

        return self::SUCCESS;
    }
}
