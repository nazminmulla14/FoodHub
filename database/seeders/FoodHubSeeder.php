<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\MenuOptionGroup;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantHour;
use App\Models\Review;
use App\Models\User;

class FoodHubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or update the restaurant.
        $restaurant = Restaurant::updateOrCreate(
            ['slug' => 'foodhub-kitchen'],
            [
                'name' => 'FoodHub Kitchen',
                'description' => 'Delicious food made fresh for you.',
                'image' => 'img/hero.png',
                'address' => 'Kolkata, India',
                'is_active' => true,
                'status' => RestaurantStatus::Active,
            ]
        );

        // 2. Create or update categories for this restaurant.
        $burgers = Category::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'slug' => 'burgers',
            ],
            ['name' => 'Burgers']
        );

        $pizza = Category::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'slug' => 'pizza',
            ],
            ['name' => 'Pizza']
        );

        $drinks = Category::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'slug' => 'drinks',
            ],
            ['name' => 'Drinks']
        );

        // 3. Create or update menu items.
        $menuItems = [
            [
                'category_id' => $burgers->id,
                'name' => 'Classic Beef Burger',
                'description' => 'A juicy burger with fresh vegetables.',
                'cuisine' => 'Burgers',
                'is_veg' => false,
                'price' => 249.00,
                'image' => 'img/menu-1.jpg',
            ],
            [
                'category_id' => $burgers->id,
                'name' => 'Chicken Burger',
                'description' => 'A crispy chicken burger with house sauce.',
                'cuisine' => 'Burgers',
                'is_veg' => false,
                'price' => 199.00,
                'image' => 'img/menu-2.jpg',
            ],
            [
                'category_id' => $pizza->id,
                'name' => 'Margherita Pizza',
                'description' => 'Classic pizza with cheese and tomato.',
                'cuisine' => 'Pizza',
                'is_veg' => true,
                'price' => 299.00,
                'image' => 'img/menu-3.jpg',
            ],
            [
                'category_id' => $pizza->id,
                'name' => 'Veggie Pizza',
                'description' => 'Pizza topped with fresh vegetables.',
                'cuisine' => 'Pizza',
                'is_veg' => true,
                'price' => 349.00,
                'image' => 'img/menu-4.jpg',
            ],
            [
                'category_id' => $drinks->id,
                'name' => 'Fresh Lemonade',
                'description' => 'A refreshing lemon drink.',
                'cuisine' => 'Drinks',
                'is_veg' => true,
                'price' => 79.00,
                'image' => 'img/menu-5.jpg',
            ],
            [
                'category_id' => $drinks->id,
                'name' => 'Cold Coffee',
                'description' => 'Chilled coffee served cold.',
                'cuisine' => 'Drinks',
                'is_veg' => true,
                'price' => 129.00,
                'image' => 'img/menu-6.jpg',
            ],
        ];

        foreach ($menuItems as $item) {
            MenuItem::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'name' => $item['name'],
                ],
                [
                    'category_id' => $item['category_id'],
                    'description' => $item['description'],
                    'cuisine' => $item['cuisine'],
                    'is_veg' => $item['is_veg'],
                    'price' => $item['price'],
                    'image' => $item['image'],
                    'is_available' => true,
                ]
            );
        }

        $burger = MenuItem::query()->where('restaurant_id', $restaurant->id)->where('name', 'Classic Beef Burger')->first();

        if ($burger && $burger->optionGroups()->doesntExist()) {
            $size = MenuOptionGroup::create([
                'menu_item_id' => $burger->id,
                'name' => 'Size',
                'is_required' => false,
                'min_select' => 0,
                'max_select' => 1,
            ]);
            $size->options()->createMany([
                ['name' => 'Regular', 'price_delta' => 0],
                ['name' => 'Large', 'price_delta' => 40],
            ]);
        }

        foreach (range(0, 6) as $weekday) {
            RestaurantHour::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'weekday' => $weekday],
                ['opens_at' => '00:00:00', 'closes_at' => '23:59:59', 'is_closed' => false]
            );
        }

        Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => CouponType::Percent,
                'value' => 10,
                'restaurant_id' => null,
                'usage_limit' => 100,
                'is_active' => true,
            ]
        );

        $reviewer = User::updateOrCreate(
            ['email' => 'reviewer@foodhub.test'],
            [
                'name' => 'Sample Reviewer',
                'password' => 'password',
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
            ]
        );

        $sampleOrder = Order::query()->where('notes', 'seed-sample-review')->first();

        if (! $sampleOrder && $burger) {
            $sampleOrder = Order::create([
                'user_id' => $reviewer->id,
                'restaurant_id' => $restaurant->id,
                'status' => OrderStatus::Completed,
                'payment_status' => PaymentStatus::Paid,
                'subtotal' => $burger->price,
                'discount' => 0,
                'tax' => 0,
                'total' => $burger->price,
                'notes' => 'seed-sample-review',
            ]);

            $sampleOrder->items()->create([
                'menu_item_id' => $burger->id,
                'name' => $burger->name,
                'price' => $burger->price,
                'quantity' => 1,
            ]);

            Review::create([
                'user_id' => $reviewer->id,
                'order_id' => $sampleOrder->id,
                'restaurant_id' => $restaurant->id,
                'rating' => 5,
                'body' => 'The burger was fresh and the kitchen was quick.',
            ]);
        }
    }
}
