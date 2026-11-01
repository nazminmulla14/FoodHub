<?php

namespace Database\Seeders;

use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoRestaurantOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::updateOrCreate(
            ['email' => 'owner@foodhub.test'],
            [
                'name' => 'Demo Owner',
                'password' => 'password',
                'role' => UserRole::RestaurantOwner,
            ]
        );

        Restaurant::updateOrCreate(
            ['slug' => 'spice-route'],
            [
                'owner_user_id' => $owner->id,
                'name' => 'Spice Route',
                'description' => 'Indian street food and curries.',
                'image' => 'img/hero.png',
                'address' => 'Mumbai, India',
                'is_active' => true,
                'status' => RestaurantStatus::Pending,
            ]
        );
    }
}
