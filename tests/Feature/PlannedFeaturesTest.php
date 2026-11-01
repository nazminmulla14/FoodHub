<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Enums\GatewayPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\RestaurantHour;
use App\Models\User;
use App\Notifications\RestaurantStatusChangedNotification;
use App\Services\Cart\CartService;
use App\Services\Orders\AdvanceOrderStatusService;
use App\Services\Orders\CancelOrderService;
use App\Services\Orders\CreatePendingOrderService;
use App\Services\Orders\ReorderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class PlannedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_advances_order_one_step_at_a_time(): void
    {
        $owner = User::factory()->create(['role' => UserRole::RestaurantOwner]);
        $restaurant = Restaurant::factory()->create(['owner_user_id' => $owner->id, 'status' => RestaurantStatus::Active]);
        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 5,
            'total' => 105,
        ]);

        $this->actingAs($owner);
        app(AdvanceOrderStatusService::class)->advance($order);
        $this->assertSame(OrderStatus::Preparing, $order->fresh()->status);

        app(AdvanceOrderStatusService::class)->advance($order->fresh());
        $this->assertSame(OrderStatus::OutForDelivery, $order->fresh()->status);

        $this->expectException(\RuntimeException::class);
        $completed = $order->fresh();
        $completed->status = OrderStatus::Completed;
        $completed->save();
        app(AdvanceOrderStatusService::class)->advance($completed->fresh());
    }

    public function test_customer_cancel_refunds_mock_payment(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Active]);
        $order = Order::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 5,
            'total' => 105,
        ]);
        Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'amount' => 105,
            'amount_paise' => 10500,
            'razorpay_order_id' => 'mock_order_cancel_'.$order->id,
            'razorpay_payment_id' => 'mock_pay_'.$order->id,
            'status' => GatewayPaymentStatus::Paid,
            'is_mock' => true,
            'paid_at' => now(),
        ]);

        $this->actingAs($user);
        app(CancelOrderService::class)->cancel($order);

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(PaymentStatus::Refunded, $order->payment_status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => GatewayPaymentStatus::Refunded->value,
        ]);
    }

    public function test_order_history_shows_cancel_state_and_cancels_from_the_page(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Active]);

        $cancelled = Order::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::Cancelled,
            'payment_status' => PaymentStatus::Refunded,
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 5,
            'total' => 105,
        ]);

        $open = Order::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 80,
            'discount' => 0,
            'tax' => 4,
            'total' => 84,
        ]);
        Payment::create([
            'order_id' => $open->id,
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'amount' => 84,
            'amount_paise' => 8400,
            'razorpay_order_id' => 'mock_order_history_'.$open->id,
            'razorpay_payment_id' => 'mock_pay_history_'.$open->id,
            'status' => GatewayPaymentStatus::Paid,
            'is_mock' => true,
            'paid_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\OrderHistoryPage::class)
            ->assertSee('Cancelled')
            ->assertSee('Refunded')
            ->assertSeeHtml('bg-danger')
            ->assertDontSeeHtml('text-bg-')
            ->call('toggleOrder', $cancelled->id)
            ->assertSee('This order was cancelled.')
            ->assertSee('The payment has been refunded.')
            ->call('toggleOrder', $open->id)
            ->assertSee('Cancel order')
            ->call('askCancel', $open->id)
            ->assertSee('Cancel order #'.$open->id)
            ->assertSee('will be refunded')
            ->call('dismissCancel')
            ->assertDontSee('will be refunded')
            ->call('askCancel', $open->id)
            ->call('cancel', $open->id)
            ->assertSee('Order #'.$open->id.' was cancelled.');

        $this->assertSame(OrderStatus::Cancelled, $open->fresh()->status);
        $this->assertSame(PaymentStatus::Refunded, $open->fresh()->payment_status);
    }

    public function test_completed_order_can_be_reviewed_once(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Active, 'is_active' => true]);
        $order = Order::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 80,
            'discount' => 0,
            'tax' => 4,
            'total' => 84,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\OrderHistoryPage::class)
            ->set('reviewRating', 4)
            ->set('reviewBody', 'Tasty and hot.')
            ->call('submitReview', $order->id)
            ->call('submitReview', $order->id);

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_reorder_skips_sold_out_items(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Active, 'is_active' => true]);
        $available = MenuItem::factory()->create(['restaurant_id' => $restaurant->id, 'is_available' => true, 'price' => 50]);
        $soldOut = MenuItem::factory()->create(['restaurant_id' => $restaurant->id, 'is_available' => false, 'name' => 'Sold Out Soup', 'price' => 40]);
        $order = Order::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 90,
            'discount' => 0,
            'tax' => 0,
            'total' => 90,
        ]);
        $order->items()->create(['menu_item_id' => $available->id, 'name' => $available->name, 'price' => 50, 'quantity' => 1]);
        $order->items()->create(['menu_item_id' => $soldOut->id, 'name' => $soldOut->name, 'price' => 40, 'quantity' => 1]);

        $this->actingAs($user);
        $result = app(ReorderService::class)->reorder($order->fresh('items'));

        $this->assertSame(1, $result['added']);
        $this->assertSame(['Sold Out Soup'], $result['skipped']);
        $this->assertSame(1, app(CartService::class)->itemCount());
    }

    public function test_coupon_reduces_order_total(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Active, 'is_active' => true]);
        $item = MenuItem::factory()->create(['restaurant_id' => $restaurant->id, 'price' => 200, 'is_available' => true]);
        Coupon::create([
            'code' => 'SAVE10',
            'type' => CouponType::Percent,
            'value' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user);
        app(CartService::class)->add($item->id, 1);

        $result = app(CreatePendingOrderService::class)->create($user, couponCode: 'save10');

        $this->assertTrue($result->success);
        $this->assertEquals(20.00, (float) $result->order->discount);
        $this->assertEquals(189.00, (float) $result->order->total);
    }

    public function test_closed_restaurant_blocks_checkout(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Active, 'is_active' => true]);
        $item = MenuItem::factory()->create(['restaurant_id' => $restaurant->id, 'is_available' => true]);

        $this->actingAs($user);
        app(CartService::class)->add($item->id, 1);

        foreach (range(0, 6) as $weekday) {
            RestaurantHour::create([
                'restaurant_id' => $restaurant->id,
                'weekday' => $weekday,
                'opens_at' => '09:00:00',
                'closes_at' => '17:00:00',
                'is_closed' => true,
            ]);
        }

        $result = app(CreatePendingOrderService::class)->create($user);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('closed', $result->message);
    }

    public function test_admin_orders_export_is_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_approving_a_restaurant_queues_owner_email(): void
    {
        Notification::fake();

        $owner = User::factory()->create(['role' => UserRole::RestaurantOwner]);
        $restaurant = Restaurant::factory()->create([
            'owner_user_id' => $owner->id,
            'status' => RestaurantStatus::Pending,
        ]);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\RestaurantsIndex::class)
            ->call('setStatus', $restaurant->id, RestaurantStatus::Active->value);

        Notification::assertSentTo($owner, RestaurantStatusChangedNotification::class);
    }
}
