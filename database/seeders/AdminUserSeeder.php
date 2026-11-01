<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Demo admin for local / CV demo.
     * Email: admin@foodhub.test
     * Password: password
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@foodhub.test'],
            [
                'name' => 'FoodHub Admin',
                'password' => 'password',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );
    }
}
