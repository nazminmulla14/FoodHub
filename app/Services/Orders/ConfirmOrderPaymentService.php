<?php

namespace App\Services\Orders;

use App\Enums\GatewayPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPlaced;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Cart\CartService;
use App\Services\Razorpay\RazorpayService;
use Illuminate\Support\Facades\DB;

class ConfirmOrderPaymentService
{
    public function __construct(
        private readonly RazorpayService $razorpay,
        private readonly CartService $cart,
    ) {}

    public function confirm(
        Order $order,
        string $razorpayOrderId,
        string $razorpayPaymentId,
        string $razorpaySignature,
        bool $mock = false,
    ): bool {
        if ($order->payment_status === PaymentStatus::Paid) {
            return true;
        }

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('razorpay_order_id', $razorpayOrderId)
            ->first();

        if (! $payment) {
            return false;
        }

        if (! $mock && $razorpaySignature !== '') {
            if (! $this->razorpay->verifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)) {
                return false;
            }
        }

        if ((int) round((float) $order->total * 100) !== (int) $payment->amount_paise) {
            return false;
        }

        try {
            DB::transaction(function () use ($order, $payment, $razorpayPaymentId, $razorpaySignature, $mock) {
            $order->refresh();

            if ($order->payment_status === PaymentStatus::Paid) {
                return;
            }

            $payment->update([
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
                'status' => GatewayPaymentStatus::Paid,
                'is_mock' => $mock,
                'paid_at' => now(),
            ]);

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Confirmed,
            ]);

            if ($order->coupon_id) {
                $coupon = Coupon::query()->lockForUpdate()->find($order->coupon_id);

                if (! $coupon || ! $coupon->isUsableFor($order->restaurant_id)) {
                    throw new \RuntimeException('Coupon is no longer valid.');
                }

                $coupon->increment('used_count');
            }

            $this->cart->clear();
            });
        } catch (\RuntimeException) {
            return false;
        }

        $order->refresh();

        if ($order->payment_status === PaymentStatus::Paid) {
            OrderPlaced::dispatch($order);
        }

        return $order->payment_status === PaymentStatus::Paid;
    }

    public function confirmFromGatewayCapture(
        Order $order,
        string $razorpayOrderId,
        string $razorpayPaymentId,
        ?int $amountPaise = null,
    ): bool {
        if ($amountPaise !== null) {
            $expected = (int) round((float) $order->total * 100);
            if ($expected !== $amountPaise) {
                return false;
            }
        }

        return $this->confirm($order, $razorpayOrderId, $razorpayPaymentId, '', mock: false);
    }
}
