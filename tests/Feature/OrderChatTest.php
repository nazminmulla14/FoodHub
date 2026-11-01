<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Admin\ChatsIndex;
use App\Livewire\OrderChatPage;
use App\Livewire\Owner\ChatsIndex as OwnerChatsIndex;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_owner_can_message_about_an_order(): void
    {
        [$customer, $owner, $order] = $this->orderForChat();

        Livewire::actingAs($customer)
            ->test(OrderChatPage::class, ['order' => $order])
            ->set('body', 'Where is my order?')
            ->call('send')
            ->assertSee('Where is my order?');

        $this->assertDatabaseHas('conversations', [
            'order_id' => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'customer_id' => $customer->id,
        ]);

        $conversationId = $order->conversation()->value('id');

        Livewire::actingAs($owner)
            ->test(OwnerChatsIndex::class)
            ->call('select', $conversationId)
            ->assertSee('Where is my order?')
            ->set('body', 'It is on the way.')
            ->call('send')
            ->assertSee('It is on the way.');

        $this->assertDatabaseHas('messages', [
            'body' => 'It is on the way.',
            'sender_id' => $owner->id,
        ]);

        Livewire::actingAs($customer)
            ->test(OrderChatPage::class, ['order' => $order->fresh()])
            ->assertSee('It is on the way.');

        $this->assertNotNull(
            $order->conversation->messages()->where('sender_id', $owner->id)->value('read_at')
        );
    }

    public function test_other_customer_cannot_open_the_thread(): void
    {
        [, , $order] = $this->orderForChat();
        $stranger = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($stranger)
            ->get(route('orders.chat', $order))
            ->assertForbidden();
    }

    public function test_admin_can_read_a_thread_but_cannot_reply(): void
    {
        [$customer, , $order] = $this->orderForChat();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        Livewire::actingAs($customer)
            ->test(OrderChatPage::class, ['order' => $order])
            ->set('body', 'Need extra sauce')
            ->call('send');

        $conversationId = $order->conversation()->value('id');

        Livewire::actingAs($admin)
            ->test(ChatsIndex::class)
            ->call('select', $conversationId)
            ->assertSee('Need extra sauce')
            ->set('body', 'We will handle it')
            ->call('send')
            ->assertForbidden();
    }

    public function test_owner_cannot_open_another_restaurants_chat(): void
    {
        [, , $order] = $this->orderForChat();
        $customer = $order->user;

        Livewire::actingAs($customer)
            ->test(OrderChatPage::class, ['order' => $order])
            ->set('body', 'Private note')
            ->call('send');

        $otherOwner = User::factory()->create(['role' => UserRole::RestaurantOwner]);
        Restaurant::factory()->create(['owner_user_id' => $otherOwner->id]);

        $inbox = Livewire::actingAs($otherOwner)
            ->test(OwnerChatsIndex::class)
            ->assertDontSee('Private note');

        $this->expectException(ModelNotFoundException::class);

        $inbox->call('select', $order->conversation()->value('id'));
    }

    /**
     * @return array{0: User, 1: User, 2: Order}
     */
    private function orderForChat(): array
    {
        $owner = User::factory()->create(['role' => UserRole::RestaurantOwner]);
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $restaurant = Restaurant::factory()->create(['owner_user_id' => $owner->id]);

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'subtotal' => 100,
            'tax' => 5,
            'total' => 105,
        ]);

        return [$customer, $owner, $order];
    }
}
