<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\Menu\SaveMenuItemService;
use Database\Seeders\FoodHubSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaveMenuItemAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_cannot_use_another_restaurants_category(): void
    {
        $this->seed(FoodHubSeeder::class);

        $owner = User::factory()->create(['role' => UserRole::RestaurantOwner]);
        $owned = Restaurant::query()->firstOrFail();
        $owned->update(['owner_user_id' => $owner->id]);

        $otherRestaurant = Restaurant::factory()->create();
        $otherCategory = Category::factory()->create(['restaurant_id' => $otherRestaurant->id]);

        $this->actingAs($owner);

        $this->expectException(AuthorizationException::class);

        app(SaveMenuItemService::class)->save([
            'category_id' => $otherCategory->id,
            'name' => 'Sneaky item',
            'price' => 10,
            'is_available' => true,
        ], user: $owner);
    }
}
