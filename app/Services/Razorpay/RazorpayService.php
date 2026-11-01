<?php

namespace App\Services\Razorpay;

use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    public function __construct(
        private readonly bool $enabled,
        private readonly ?string $keyId = null,
        private readonly ?string $keySecret = null,
        private readonly ?string $webhookSecret = null,
    ) {}

    public function isEnabled(): bool
    {
        return $this->enabled && ! empty($this->keyId) && ! empty($this->keySecret);
    }

    public function keyId(): ?string
    {
        return $this->keyId;
    }

    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        if (empty($this->webhookSecret) || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, (string) $this->webhookSecret);

        return hash_equals($expected, $signature);
    }

    public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
    {
        $payload = [
            'amount' => $amountPaise,
            'currency' => 'INR',
            'receipt' => $receipt,
        ];

        if ($notes !== []) {
            $payload['notes'] = $notes;
        }

        return $this->client()->order->create($payload)->toArray();
    }

    public function refundPayment(string $paymentId, int $amountPaise): void
    {
        $this->client()->payment->fetch($paymentId)->refund([
            'amount' => $amountPaise,
        ]);
    }

    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        try {
            $this->client()->utility->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }

    private function client(): Api
    {
        return new Api((string) $this->keyId, (string) $this->keySecret);
    }
}
