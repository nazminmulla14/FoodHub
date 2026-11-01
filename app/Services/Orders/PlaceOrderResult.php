<?php

namespace App\Services\Orders;

use App\Models\Order;

readonly class PlaceOrderResult
{
    public function __construct(
        public bool $success,
        public ?Order $order = null,
        public ?string $message = null,
    ) {}
}
