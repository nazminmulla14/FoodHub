<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Services\Orders\PlaceOrderService;
use Database\Seeders\FoodHubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_order_from_cart(): void
    {
        Queue::fake();

        $this->seed(FoodHubSeeder::class);

        $user = User::factory()->create(['role' => UserRole::Customer]);
        $menuItem = MenuItem::query()->firstOrFail();

        $this->actingAs($user);

        app(CartService::class)->add($menuItem->id, 2);

        $result = app(PlaceOrderService::class)->place($user);

        $this->assertTrue($result->success);
        $this->assertNotNull($result->order);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
        $this->assertDatabaseHas('order_items', ['menu_item_id' => $menuItem->id]);
        $this->assertDatabaseCount('cart_items', 0);
    }
}
