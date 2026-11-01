<?php

namespace App\Livewire\Admin;

use App\Concerns\ScopesOwnedRestaurants;
use App\Enums\BookingStatus;
use App\Models\TableBooking;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class BookingsIndex extends Component
{
    use ScopesOwnedRestaurants, WithPagination;

    #[On('panel-restaurant-changed')]
    public function refreshForRestaurant(): void
    {
        $this->resetPage();
    }

    public function updateStatus(int $bookingId, string $status): void
    {
        $booking = TableBooking::query()->findOrFail($bookingId);
        $this->authorize('update', $booking);

        $booking->status = BookingStatus::from($status);
        $booking->save();
    }

    public function render()
    {
        $this->authorize('viewAny', TableBooking::class);

        $bookings = $this->scopeOwnedRestaurants(
            TableBooking::query()->with('restaurant')
        )
            ->latest('booked_at')
            ->paginate(15);

        return view('livewire.admin.bookings-index', [
            'bookings' => $bookings,
            'statuses' => BookingStatus::cases(),
        ]);
    }
}
