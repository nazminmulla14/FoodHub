<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Paid => 'bg-success',
            self::Pending => 'bg-warning text-dark',
            self::Failed => 'bg-danger',
            self::Refunded => 'bg-secondary',
        };
    }
}
