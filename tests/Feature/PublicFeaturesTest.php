<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Jobs\SendBookingConfirmation;
use App\Jobs\SendContactMessageAlert;
use App\Models\MenuItem;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\FoodHubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class PublicFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_queues_admin_alert(): void
    {
        Queue::fake();
        $this->seed(FoodHubSeeder::class);

        Livewire::test(\App\Livewire\ContactPage::class)
            ->set('name', 'Nazmin')
            ->set('email', 'nazmin@example.com')
            ->set('subject', 'Hello')
            ->set('message', 'Testing contact form')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contact_messages', ['email' => 'nazmin@example.com']);
        Queue::assertPushed(SendContactMessageAlert::class);
    }

    public function test_booking_form_creates_booking(): void
    {
        Queue::fake();
        $this->seed(FoodHubSeeder::class);

        Livewire::test(\App\Livewire\BookTableForm::class)
            ->set('guest_name', 'Guest')
            ->set('guest_email', 'guest@example.com')
            ->set('party_size', 2)
            ->set('booked_at', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('book')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('table_bookings', ['guest_email' => 'guest@example.com']);
        Queue::assertPushed(SendBookingConfirmation::class);
    }

    public function test_admin_can_open_contact_inbox(): void
    {
        $this->seed(FoodHubSeeder::class);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.contact-messages'))
            ->assertOk();
    }

    public function test_home_page_ranks_menu_items_by_units_sold(): void
    {
        $restaurant = Restaurant::factory()->create([
            'status' => RestaurantStatus::Active,
            'is_active' => true,
        ]);

        $slow = MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Slow Dish',
            'created_at' => now(),
        ]);
        $hot = MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Hot Dish',
            'created_at' => now()->subDay(),
        ]);

        $order = Order::query()->create([
            'user_id' => User::factory()->create()->id,
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 50,
            'tax' => 0,
            'total' => 50,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'menu_item_id' => $hot->id,
            'name' => 'Hot Dish',
            'price' => 10,
            'quantity' => 5,
        ]);

        Livewire::test(\App\Livewire\HomePage::class)
            ->assertSeeInOrder(['Hot Dish', 'Slow Dish']);
    }

    public function test_admin_can_view_newsletter_subscribers(): void
    {
        $admin = User::factory()->admin()->create();
        NewsletterSubscriber::query()->create(['email' => 'reader@example.com']);

        $this->actingAs($admin)
            ->get(route('admin.newsletter'))
            ->assertOk()
            ->assertSee('reader@example.com');

        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)
            ->get(route('admin.newsletter'))
            ->assertForbidden();
    }
}
