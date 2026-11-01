<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Support\RestaurantAccess;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id
            || RestaurantAccess::managesRestaurant($user, $order->restaurant_id);
    }

    public function update(User $user, Order $order): bool
    {
        return RestaurantAccess::managesRestaurant($user, $order->restaurant_id);
    }

    public function advance(User $user, Order $order): bool
    {
        return RestaurantAccess::managesRestaurant($user, $order->restaurant_id);
    }

    public function message(User $user, Order $order): bool
    {
        if ($order->user_id === $user->id) {
            return true;
        }

        return $user->isRestaurantOwner()
            && $user->ownedRestaurants()->whereKey($order->restaurant_id)->exists();
    }

    public function cancel(User $user, Order $order): bool
    {
        if (! $order->status->canBeCancelled()) {
            return false;
        }

        return $order->user_id === $user->id
            || RestaurantAccess::managesRestaurant($user, $order->restaurant_id);
    }
}
