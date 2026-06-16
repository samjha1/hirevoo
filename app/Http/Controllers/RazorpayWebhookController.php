<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\CandidatePlanCheckoutService;
use App\Services\EmployerPlanCheckoutService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(
        protected RazorpayService $razorpay,
        protected CandidatePlanCheckoutService $candidateCheckout,
        protected EmployerPlanCheckoutService $employerCheckout,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        if (! $this->razorpay->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $event = $request->json()->all();
        $eventName = (string) ($event['event'] ?? '');

        if (! in_array($eventName, ['payment.captured', 'payment.authorized', 'order.paid'], true)) {
            return response()->json(['message' => 'Ignored.']);
        }

        $paymentEntity = $event['payload']['payment']['entity'] ?? null;
        $orderEntity = $event['payload']['order']['entity'] ?? null;
        $orderId = '';
        $paymentId = '';

        if (is_array($paymentEntity)) {
            $orderId = (string) ($paymentEntity['order_id'] ?? '');
            $paymentId = (string) ($paymentEntity['id'] ?? '');
        } elseif (is_array($orderEntity)) {
            $orderId = (string) ($orderEntity['id'] ?? '');
            $successful = $this->razorpay->findSuccessfulPaymentForOrder($orderId);
            if ($successful === null) {
                return response()->json(['message' => 'No captured payment for order.']);
            }
            $paymentId = $successful['id'];
        }

        if ($orderId === '' || $paymentId === '') {
            return response()->json(['message' => 'Missing ids.']);
        }

        $payment = Payment::query()
            ->where('payment_gateway', Payment::GATEWAY_RAZORPAY)
            ->where('payment_reference', $orderId)
            ->first();

        if ($payment === null) {
            return response()->json(['message' => 'Payment record not found.']);
        }

        try {
            if ($payment->type === CandidatePlanCheckoutService::PAYMENT_TYPE) {
                $this->candidateCheckout->completeFromGateway($payment, $paymentId, 'webhook:'.$eventName);
            } elseif ($payment->type === EmployerPlanCheckoutService::PAYMENT_TYPE) {
                $this->employerCheckout->completeFromGateway($payment, $paymentId, 'webhook:'.$eventName);
            } else {
                return response()->json(['message' => 'Unsupported payment type.']);
            }

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $e) {
            Log::error('Razorpay webhook completion failed', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Completion failed.'], 500);
        }
    }
}
