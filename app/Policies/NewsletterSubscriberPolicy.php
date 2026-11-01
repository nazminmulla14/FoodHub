<?php

namespace App\Policies;

use App\Models\User;

class NewsletterSubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
