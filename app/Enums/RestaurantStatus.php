<?php

namespace App\Enums;

enum RestaurantStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';

    public function isPubliclyVisible(): bool
    {
        return $this === self::Active;
    }
}
