<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use App\Support\RestaurantAccess;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isRestaurantOwner();
    }

    public function reply(User $user, Review $review): bool
    {
        return RestaurantAccess::managesRestaurant($user, $review->restaurant_id);
    }
}
