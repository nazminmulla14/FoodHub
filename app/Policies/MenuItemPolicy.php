<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;
use App\Support\RestaurantAccess;

class MenuItemPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isRestaurantOwner();
    }

    public function update(User $user, MenuItem $menuItem): bool
    {
        return RestaurantAccess::managesRestaurant($user, $menuItem->restaurant_id);
    }

    public function delete(User $user, MenuItem $menuItem): bool
    {
        return RestaurantAccess::managesRestaurant($user, $menuItem->restaurant_id);
    }
}
