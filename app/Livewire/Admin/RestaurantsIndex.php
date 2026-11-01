<?php

namespace App\Livewire\Admin;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use App\Notifications\RestaurantStatusChangedNotification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class RestaurantsIndex extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setStatus(int $restaurantId, string $status): void
    {
        $restaurant = Restaurant::query()->with('owner')->findOrFail($restaurantId);
        $this->authorize('update', $restaurant);

        $next = RestaurantStatus::from($status);

        if ($restaurant->status === $next) {
            return;
        }

        $restaurant->status = $next;
        $restaurant->save();

        if (in_array($next, [RestaurantStatus::Active, RestaurantStatus::Suspended], true) && $restaurant->owner) {
            $restaurant->owner->notify(new RestaurantStatusChangedNotification($restaurant));
        }

        session()->flash('admin_success', 'Restaurant status updated.');
    }

    public function render()
    {
        $restaurants = Restaurant::query()
            ->with('owner')
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.restaurants-index', [
            'restaurants' => $restaurants,
            'statuses' => RestaurantStatus::cases(),
        ]);
    }
}
