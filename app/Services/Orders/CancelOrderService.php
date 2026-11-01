<?php

namespace App\Services\Orders;

use App\Enums\GatewayPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\Razorpay\RazorpayService;
use Illuminate\Support\Facades\DB;

class CancelOrderService
{
    public function __construct(
        private readonly RazorpayService $razorpay,
    ) {}

    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order->refresh();

            if (! $order->status->canBeCancelled()) {
                throw new \RuntimeException('This order can no longer be cancelled.');
            }

            $payment = $order->payments()
                ->where('status', GatewayPaymentStatus::Paid)
                ->latest('id')
                ->first();

            if ($payment && $order->payment_status === PaymentStatus::Paid) {
                if (! $payment->is_mock) {
                    if (! $payment->razorpay_payment_id || ! $this->razorpay->isEnabled()) {
                        throw new \RuntimeException('This payment cannot be refunded right now.');
                    }

                    $this->razorpay->refundPayment($payment->razorpay_payment_id, (int) $payment->amount_paise);
                }

                $payment->update(['status' => GatewayPaymentStatus::Refunded]);
                $order->payment_status = PaymentStatus::Refunded;

                if ($order->coupon_id) {
                    Coupon::query()
                        ->where('id', $order->coupon_id)
                        ->where('used_count', '>', 0)
                        ->decrement('used_count');
                }
            }

            $order->status = OrderStatus::Cancelled;
            $order->save();

            return $order;
        });
    }
}
