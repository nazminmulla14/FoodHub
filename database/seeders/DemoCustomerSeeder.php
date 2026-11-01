<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCustomerSeeder extends Seeder
{
    /**
     * Email: customer@foodhub.test
     * Password: password
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'customer@foodhub.test'],
            [
                'name' => 'Demo Customer',
                'password' => 'password',
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
            ]
        );
    }
}
