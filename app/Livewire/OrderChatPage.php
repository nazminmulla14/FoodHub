<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\Chat\OrderChat;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrderChatPage extends Component
{
    public Order $order;

    public string $body = '';

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $this->order = $order->load('restaurant');
    }

    public function send(OrderChat $chat): void
    {
        $this->authorize('message', $this->order);

        $validated = $this->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $chat->send($this->order, auth()->user(), $validated['body']);
        $this->reset('body');
    }

    public function render(OrderChat $chat)
    {
        $conversation = $this->order->conversation()
            ->with(['messages.sender'])
            ->first();

        if ($conversation) {
            $chat->markRead($conversation, auth()->user());
            $conversation->load(['messages.sender']);
        }

        return view('livewire.order-chat-page', [
            'conversation' => $conversation,
        ]);
    }
}
