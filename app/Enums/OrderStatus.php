<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case OutForDelivery = 'out_for_delivery';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::OutForDelivery => 'Out for delivery',
            self::Completed => 'Delivered',
            self::Cancelled => 'Cancelled',
            default => ucfirst($this->value),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning text-dark',
            self::Confirmed => 'bg-info text-dark',
            self::Preparing, self::OutForDelivery => 'bg-primary',
            self::Completed => 'bg-success',
            self::Cancelled => 'bg-danger',
        };
    }

    /**
     * @return list<self>
     */
    public static function timeline(): array
    {
        return [
            self::Pending,
            self::Confirmed,
            self::Preparing,
            self::OutForDelivery,
            self::Completed,
        ];
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Pending => self::Confirmed,
            self::Confirmed => self::Preparing,
            self::Preparing => self::OutForDelivery,
            self::OutForDelivery => self::Completed,
            default => null,
        };
    }

    public function canBeCancelled(): bool
    {
        return $this === self::Pending || $this === self::Confirmed;
    }

    public function timelineIndex(): ?int
    {
        $index = array_search($this, self::timeline(), true);

        return $index === false ? null : $index;
    }
}
