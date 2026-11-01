<?php

namespace App\Policies;

use App\Models\TableBooking;
use App\Models\User;
use App\Support\RestaurantAccess;

class TableBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isRestaurantOwner();
    }

    public function update(User $user, TableBooking $tableBooking): bool
    {
        return RestaurantAccess::managesRestaurant($user, $tableBooking->restaurant_id);
    }
}
