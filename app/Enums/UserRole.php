<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case RestaurantOwner = 'restaurant_owner';
    case Admin = 'admin';

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function isRestaurantOwner(): bool
    {
        return $this === self::RestaurantOwner;
    }
}
