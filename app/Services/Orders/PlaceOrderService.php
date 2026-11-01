<?php

namespace App\Services\Orders;

use App\Models\User;

/**
 * @deprecated Use InitiateOrderPaymentService + ConfirmOrderPaymentService for Razorpay flow.
 */
class PlaceOrderService
{
    public function __construct(
        private readonly InitiateOrderPaymentService $initiate,
        private readonly ConfirmOrderPaymentService $confirm,
    ) {}

    public function place(User $user, ?string $notes = null, ?string $deliveryAddress = null, ?string $couponCode = null): PlaceOrderResult
    {
        $init = $this->initiate->initiate($user, $notes, $deliveryAddress, $couponCode);

        if (! $init['success'] || ! $init['order'] || ! $init['payment']) {
            return new PlaceOrderResult(false, message: $init['message'] ?? 'Payment failed.');
        }

        $checkout = $init['checkout'];
        $mockPaymentId = 'mock_pay_'.$init['order']->id;
        $mockSignature = 'mock_sig';

        $ok = $this->confirm->confirm(
            $init['order'],
            $checkout['order_id'],
            $mockPaymentId,
            $mockSignature,
            mock: true,
        );

        if (! $ok) {
            return new PlaceOrderResult(false, message: 'Could not confirm payment.');
        }

        return new PlaceOrderResult(true, order: $init['order']->fresh());
    }
}
