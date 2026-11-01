<?php

namespace App\Services\Menu;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use App\Support\RestaurantAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveMenuItemService
{
    public function save(
        array $attributes,
        ?UploadedFile $image = null,
        ?MenuItem $menuItem = null,
        ?User $user = null,
    ): MenuItem {
        $category = Category::query()->findOrFail($attributes['category_id']);

        $user ??= auth()->user();

        if (! $user || ! RestaurantAccess::managesRestaurant($user, $category->restaurant_id)) {
            throw new AuthorizationException('You cannot assign this category.');
        }

        if ($menuItem && $menuItem->restaurant_id !== $category->restaurant_id) {
            throw new AuthorizationException('Category must belong to the same restaurant as the menu item.');
        }

        $data = [
            'restaurant_id' => $category->restaurant_id,
            'category_id' => $category->id,
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'cuisine' => $attributes['cuisine'] ?? null,
            'is_veg' => (bool) ($attributes['is_veg'] ?? false),
            'price' => $attributes['price'],
            'is_available' => (bool) ($attributes['is_available'] ?? true),
        ];

        if ($image) {
            $path = $image->store('menu-items', 'public');
            $data['image'] = $path;
        }

        if ($menuItem) {
            if ($image && $menuItem->image && str_starts_with($menuItem->image, 'menu-items/')) {
                Storage::disk('public')->delete($menuItem->image);
            }

            $menuItem->update($data);

            return $menuItem->fresh();
        }

        if (! isset($data['image'])) {
            $data['image'] = 'img/menu-1.jpg';
        }

        return MenuItem::create($data);
    }
}
