<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\EmployerPlanCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanPaymentController extends Controller
{
    public function __construct(
        protected EmployerPlanCheckoutService $checkoutService,
    ) {}

    public function index(): View
    {
        return view('hirevo.admin.plan-payments.index', [
            'payments' => $this->checkoutService->pendingPayments(),
        ]);
    }

    public function approve(Payment $payment): RedirectResponse
    {
        if ($payment->type !== EmployerPlanCheckoutService::PAYMENT_TYPE) {
            return back()->with('error', 'This payment is not an employer subscription.');
        }

        if ($payment->status !== Payment::STATUS_PENDING) {
            return back()->with('error', 'Only pending payments can be approved.');
        }

        try {
            $this->checkoutService->completePayment($payment);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $planKey = (string) ($payment->fresh()->meta['plan_key'] ?? '');

        return back()->with('success', "Payment #{$payment->id} approved. Plan \"{$planKey}\" is now active for {$payment->user?->name}.");
    }
}
