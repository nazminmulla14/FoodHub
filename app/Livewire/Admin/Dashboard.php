<?php

namespace App\Livewire\Admin;

use App\Concerns\ScopesOwnedRestaurants;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableBooking;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    use ScopesOwnedRestaurants;

    #[On('panel-restaurant-changed')]
    public function refreshForRestaurant(): void
    {
        //
    }

    public function render()
    {
        $ordersQuery = $this->scopeOwnedRestaurants(Order::query());
        $bookingsQuery = $this->scopeOwnedRestaurants(TableBooking::query());
        $paidOrders = (clone $ordersQuery)
            ->where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', OrderStatus::Cancelled);

        $topDishes = OrderItem::query()
            ->selectRaw('name, sum(quantity) as units')
            ->whereIn('order_id', (clone $paidOrders)->select('orders.id'))
            ->groupBy('name')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'ordersCount' => (clone $ordersQuery)->count(),
            'pendingBookings' => (clone $bookingsQuery)->where('status', 'pending')->count(),
            'customersCount' => User::query()->where('role', UserRole::Customer)->count(),
            'todayRevenue' => (clone $paidOrders)->whereDate('created_at', today())->sum('total'),
            'weekRevenue' => (clone $paidOrders)->where('created_at', '>=', now()->startOfWeek())->sum('total'),
            'bookingCount' => (clone $bookingsQuery)->count(),
            'topDishes' => $topDishes,
        ]);
    }
}
