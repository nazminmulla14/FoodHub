<?php

namespace App\Livewire\Admin;

use App\Concerns\ScopesOwnedRestaurants;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Orders\AdvanceOrderStatusService;
use App\Services\Orders\CancelOrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class OrdersIndex extends Component
{
    use ScopesOwnedRestaurants, WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[On('panel-restaurant-changed')]
    public function refreshForRestaurant(): void
    {
        $this->resetPage();
    }

    public function advance(int $orderId, AdvanceOrderStatusService $advance): void
    {
        $order = Order::query()->findOrFail($orderId);
        $this->authorize('advance', $order);

        try {
            $advance->advance($order);
        } catch (\RuntimeException $e) {
            session()->flash('admin_error', $e->getMessage());
        }
    }

    public function cancel(int $orderId, CancelOrderService $cancel): void
    {
        $order = Order::query()->findOrFail($orderId);
        $this->authorize('cancel', $order);

        try {
            $cancel->cancel($order);
        } catch (\RuntimeException $e) {
            session()->flash('admin_error', $e->getMessage());
        }
    }

    public function render()
    {
        $orders = $this->scopeOwnedRestaurants(
            Order::query()->with(['user', 'restaurant', 'items'])
        )
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.orders-index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
