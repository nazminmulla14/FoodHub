<?php

namespace App\Concerns;

use App\Support\AdminRestaurantContext;
use Illuminate\Database\Eloquent\Builder;

trait ScopesOwnedRestaurants
{
    protected function scopeOwnedRestaurants(Builder $query, string $column = 'restaurant_id'): Builder
    {
        $user = auth()->user();
        $context = app(AdminRestaurantContext::class);

        if ($user?->isAdmin() && request()->routeIs('admin.*')) {
            $selectedId = $context->selectedId();

            return $selectedId
                ? $query->where($column, $selectedId)
                : $query;
        }

        if ($user?->isRestaurantOwner()) {
            $ids = $user->ownedRestaurants()->pluck('id');
            $selectedId = $context->selectedId();

            if ($selectedId && $ids->contains($selectedId)) {
                return $query->where($column, $selectedId);
            }

            return $query->whereIn($column, $ids);
        }

        return $query;
    }
}
