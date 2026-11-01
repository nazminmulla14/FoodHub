<?php

namespace App\Support;

use App\Models\Restaurant;
use App\Models\User;

class RestaurantAccess
{
    public static function managesRestaurant(User $user, int $restaurantId): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isRestaurantOwner()) {
            return $user->ownedRestaurants()->whereKey($restaurantId)->exists();
        }

        return false;
    }

    public static function managesMenuItem(User $user, Restaurant $restaurant): bool
    {
        return self::managesRestaurant($user, $restaurant->id);
    }
}
