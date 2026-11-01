<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;

class AdvanceOrderStatusService
{
    public function advance(Order $order): Order
    {
        $next = $order->status->next();

        if (! $next) {
            throw new \RuntimeException('This order cannot move to another status.');
        }

        $order->status = $next;
        $order->save();

        return $order;
    }

    public function assertTransition(OrderStatus $from, OrderStatus $to): void
    {
        if ($from->next() !== $to) {
            throw new \RuntimeException('That status change is not allowed.');
        }
    }
}
