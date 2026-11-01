<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isRestaurantOwner();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isRestaurantOwner();
    }

    public function update(User $user, Category $category): bool
    {
        return \App\Support\RestaurantAccess::managesRestaurant($user, $category->restaurant_id);
    }

    public function delete(User $user, Category $category): bool
    {
        return \App\Support\RestaurantAccess::managesRestaurant($user, $category->restaurant_id);
    }
}
