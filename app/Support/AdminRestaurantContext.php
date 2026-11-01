<?php

namespace App\Support;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminRestaurantContext
{
    public const SESSION_KEY = 'foodhub_panel_restaurant_id';

    /**
     * @return Collection<int, Restaurant>
     */
    public function manageableRestaurants(?User $user = null): Collection
    {
        $user ??= auth()->user();

        if (! $user) {
            return collect();
        }

        if ($user->isAdmin()) {
            return Restaurant::query()->orderBy('name')->get();
        }

        if ($user->isRestaurantOwner()) {
            return $user->ownedRestaurants()->orderBy('name')->get();
        }

        return collect();
    }

    public function selected(?User $user = null): ?Restaurant
    {
        $restaurants = $this->manageableRestaurants($user);

        if ($restaurants->isEmpty()) {
            return null;
        }

        $sessionId = session(self::SESSION_KEY);
        $selected = $sessionId
            ? $restaurants->firstWhere('id', (int) $sessionId)
            : null;

        if (! $selected) {
            $selected = $restaurants->first();
            session([self::SESSION_KEY => $selected->id]);
        }

        return $selected;
    }

    public function selectedId(?User $user = null): ?int
    {
        return $this->selected($user)?->id;
    }

    public function setSelected(int $restaurantId, ?User $user = null): void
    {
        $user ??= auth()->user();

        if (! $user || ! RestaurantAccess::managesRestaurant($user, $restaurantId)) {
            abort(403);
        }

        session([self::SESSION_KEY => $restaurantId]);
    }
}
