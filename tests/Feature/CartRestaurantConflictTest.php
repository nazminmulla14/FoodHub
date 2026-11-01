<?php

namespace Tests\Feature;

use App\Exceptions\CartRestaurantConflictException;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\Cart\CartService;
use Database\Seeders\FoodHubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartRestaurantConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_from_second_restaurant_raises_conflict(): void
    {
        $this->seed(FoodHubSeeder::class);

        $firstRestaurant = Restaurant::query()->firstOrFail();
        $secondRestaurant = Restaurant::factory()->create(['name' => 'Second Spot', 'slug' => 'second-spot']);

        $firstItem = MenuItem::query()->where('restaurant_id', $firstRestaurant->id)->firstOrFail();
        $secondItem = MenuItem::factory()->create(['restaurant_id' => $secondRestaurant->id]);

        $cart = app(CartService::class);
        $cart->add($firstItem->id);

        $this->expectException(CartRestaurantConflictException::class);
        $cart->add($secondItem->id);
    }

    public function test_replace_flag_clears_cart_for_new_restaurant(): void
    {
        $this->seed(FoodHubSeeder::class);

        $firstRestaurant = Restaurant::query()->firstOrFail();
        $secondRestaurant = Restaurant::factory()->create(['name' => 'Second Spot', 'slug' => 'second-spot-2']);

        $firstItem = MenuItem::query()->where('restaurant_id', $firstRestaurant->id)->firstOrFail();
        $secondItem = MenuItem::factory()->create(['restaurant_id' => $secondRestaurant->id]);

        $cart = app(CartService::class);
        $cart->add($firstItem->id);
        $cart->add($secondItem->id, replaceIfDifferentRestaurant: true);

        $this->assertSame(1, $cart->itemCount());
        $this->assertSame($secondItem->restaurant_id, $cart->restaurantIdInCart());
    }
}
