<?php

namespace App\Enums;

enum GatewayPaymentStatus: string
{
    case Created = 'created';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
}
