<?php

namespace App\Livewire;

use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RegisterRestaurantOwnerPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $restaurant_name = '';

    public string $restaurant_address = '';

    public ?string $errorMessage = null;

    public function register(): void
    {
        $this->errorMessage = null;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'restaurant_name' => ['required', 'string', 'max:120'],
            'restaurant_address' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $this->uniqueRestaurantSlug($validated['restaurant_name']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => UserRole::RestaurantOwner,
        ]);

        Restaurant::create([
            'owner_user_id' => $user->id,
            'name' => $validated['restaurant_name'],
            'slug' => $slug,
            'description' => 'Welcome to '.$validated['restaurant_name'],
            'address' => $validated['restaurant_address'] ?: null,
            'image' => 'img/hero.png',
            'is_active' => true,
            'status' => RestaurantStatus::Pending,
        ]);

        Auth::login($user);

        session()->flash('owner_registered', true);

        $this->redirect(route('owner.dashboard'), navigate: true);
    }

    private function uniqueRestaurantSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Restaurant::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.register-restaurant-owner-page');
    }
}
