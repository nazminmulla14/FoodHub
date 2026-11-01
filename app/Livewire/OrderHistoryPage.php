<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Review;
use App\Services\Orders\CancelOrderService;
use App\Services\Orders\ReorderService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class OrderHistoryPage extends Component
{
    use WithPagination;

    public ?int $expandedOrderId = null;

    public int $reviewRating = 5;

    public string $reviewBody = '';

    public ?string $notice = null;

    public ?int $confirmingCancelId = null;

    public function toggleOrder(int $orderId): void
    {
        $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
    }

    public function askCancel(int $orderId): void
    {
        $order = Order::query()->where('user_id', auth()->id())->findOrFail($orderId);
        $this->authorize('cancel', $order);
        $this->confirmingCancelId = $order->id;
    }

    public function dismissCancel(): void
    {
        $this->confirmingCancelId = null;
    }

    public function cancel(int $orderId, CancelOrderService $cancel): void
    {
        $order = Order::query()->where('user_id', auth()->id())->findOrFail($orderId);
        $this->authorize('cancel', $order);

        try {
            $cancel->cancel($order);
            $this->notice = 'Order #'.$order->id.' was cancelled.';
        } catch (\RuntimeException $e) {
            $this->notice = $e->getMessage();
        }

        $this->confirmingCancelId = null;
    }

    public function reorder(int $orderId, ReorderService $reorder): void
    {
        $order = Order::query()->where('user_id', auth()->id())->findOrFail($orderId);
        $this->authorize('view', $order);

        $result = $reorder->reorder($order);
        $this->dispatch('cart-updated');

        $message = $result['added'].' item(s) added to your cart.';

        if ($result['skipped'] !== []) {
            $message .= ' Skipped: '.implode(', ', $result['skipped']).'.';
        }

        session()->flash('cart_notice', $message);
        $this->redirect(route('cart'), navigate: true);
    }

    public function submitReview(int $orderId): void
    {
        $order = Order::query()->where('user_id', auth()->id())->findOrFail($orderId);

        if (! Review::canReview($order, auth()->user())) {
            $this->notice = 'This order cannot be reviewed.';

            return;
        }

        $validated = $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewBody' => ['required', 'string', 'max:1000'],
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'order_id' => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'rating' => $validated['reviewRating'],
            'body' => $validated['reviewBody'],
        ]);

        $this->reviewBody = '';
        $this->reviewRating = 5;
        $this->notice = 'Thanks for your review.';
    }

    public function render()
    {
        $orders = Order::query()
            ->with([
                'restaurant',
                'items',
                'review',
                'conversation' => function ($query) {
                    $query->withCount([
                        'messages as unread_count' => function ($messages) {
                            $messages->where('sender_id', '!=', auth()->id())
                                ->whereNull('read_at');
                        },
                    ]);
                },
            ])
            ->when(! auth()->user()->isAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->latest()
            ->paginate(10);

        return view('livewire.order-history-page', [
            'orders' => $orders,
            'confirmingOrder' => $this->confirmingCancelId
                ? $orders->getCollection()->firstWhere('id', $this->confirmingCancelId)
                : null,
        ]);
    }
}
