<?php

namespace App\Services\Orders;

use App\Enums\GatewayPaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Razorpay\RazorpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InitiateOrderPaymentService
{
    public function __construct(
        private readonly CreatePendingOrderService $createOrder,
        private readonly RazorpayService $razorpay,
    ) {}

    /**
     * @return array{success:bool,message?:string,order?:Order,payment?:Payment,checkout?:array}
     */
    public function initiate(User $user, ?string $notes = null, ?string $deliveryAddress = null, ?string $couponCode = null): array
    {
        $result = $this->createOrder->create($user, $notes, $deliveryAddress, $couponCode);

        if (! $result->success || ! $result->order) {
            return ['success' => false, 'message' => $result->message ?? 'Could not create order.'];
        }

        $order = $result->order;
        $amountPaise = (int) round((float) $order->total * 100);
        $receipt = 'order_'.$order->id;

        $gatewayPayload = null;

        if ($this->razorpay->isEnabled()) {
            $gatewayPayload = $this->razorpay->createOrder($amountPaise, $receipt, [
                'order_id' => (string) $order->id,
                'restaurant_id' => (string) $order->restaurant_id,
            ]);
            $razorpayOrderId = (string) $gatewayPayload['id'];
        } else {
            $razorpayOrderId = 'mock_order_'.Str::uuid();
        }

        $payment = DB::transaction(function () use ($order, $user, $amountPaise, $razorpayOrderId, $gatewayPayload) {
            return Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'restaurant_id' => $order->restaurant_id,
                'amount' => $order->total,
                'amount_paise' => $amountPaise,
                'currency' => 'INR',
                'razorpay_order_id' => $razorpayOrderId,
                'status' => GatewayPaymentStatus::Created,
                'is_mock' => ! $this->razorpay->isEnabled(),
                'gateway_payload' => $gatewayPayload,
            ]);
        });

        return [
            'success' => true,
            'order' => $order,
            'payment' => $payment,
            'checkout' => [
                'key' => $this->razorpay->keyId(),
                'order_id' => $razorpayOrderId,
                'amount_paise' => $amountPaise,
                'currency' => 'INR',
                'mock' => ! $this->razorpay->isEnabled(),
                'foodhub_order_id' => $order->id,
            ],
        ];
    }
}
