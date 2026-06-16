<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    public function isConfigured(): bool
    {
        return filled(config('razorpay.key_id')) && filled(config('razorpay.key_secret'));
    }

    /**
     * @return array{order_id: string, amount: int, currency: string}
     */
    public function createOrder(int $amountPaise, string $currency, string $receipt, array $notes = []): array
    {
        $this->assertConfigured();

        if ($amountPaise < 100) {
            throw new InvalidArgumentException('Amount must be at least 100 paise.');
        }

        try {
            $order = $this->api()->order->create([
                'receipt' => $receipt,
                'amount' => $amountPaise,
                'currency' => strtoupper($currency),
                'notes' => $notes,
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay order creation failed', ['error' => $e->getMessage()]);

            throw new InvalidArgumentException('Unable to create payment order. Please try again.');
        }

        return [
            'order_id' => (string) $order['id'],
            'amount' => (int) $order['amount'],
            'currency' => (string) $order['currency'],
        ];
    }

    public function verifyCheckoutSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $this->assertConfigured();

        if ($orderId === '' || $paymentId === '' || $signature === '') {
            return false;
        }

        try {
            $this->api()->utility->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay signature verification failed', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function paymentsForOrder(string $orderId): array
    {
        $this->assertConfigured();

        try {
            $collection = $this->api()->order->fetch($orderId)->payments();

            return $collection['items'] ?? [];
        } catch (\Throwable $e) {
            Log::error('Razorpay fetch order payments failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return array{id: string, status: string, captured: bool}|null
     */
    public function findSuccessfulPaymentForOrder(string $orderId): ?array
    {
        foreach ($this->paymentsForOrder($orderId) as $item) {
            $status = (string) ($item['status'] ?? '');
            $captured = (bool) ($item['captured'] ?? false);

            if ($status === 'captured' || ($status === 'authorized' && $captured)) {
                return [
                    'id' => (string) $item['id'],
                    'status' => $status,
                    'captured' => $captured,
                ];
            }
        }

        return null;
    }

    /**
     * @return array{id: string, error_description: string}|null
     */
    public function findLatestFailedPaymentForOrder(string $orderId): ?array
    {
        $items = $this->paymentsForOrder($orderId);
        $latest = null;

        foreach ($items as $item) {
            if ((string) ($item['status'] ?? '') !== 'failed') {
                continue;
            }

            $latest = $item;
        }

        if ($latest === null) {
            return null;
        }

        return [
            'id' => (string) $latest['id'],
            'error_description' => (string) ($latest['error_description'] ?? 'Payment failed at gateway.'),
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) config('razorpay.webhook_secret', '');
        if ($secret === '' || $signature === '') {
            return false;
        }

        try {
            $this->api()->utility->verifyWebhookSignature($payload, $signature, $secret);

            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay webhook signature failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function api(): Api
    {
        return new Api(
            (string) config('razorpay.key_id'),
            (string) config('razorpay.key_secret'),
        );
    }

    private function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new InvalidArgumentException('Payment gateway is not configured.');
        }
    }
}
