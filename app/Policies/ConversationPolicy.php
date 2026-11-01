<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isRestaurantOwner();
    }

    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->isAdmin() || $conversation->customer_id === $user->id) {
            return true;
        }

        return $user->isRestaurantOwner()
            && $user->ownedRestaurants()->whereKey($conversation->restaurant_id)->exists();
    }
}
