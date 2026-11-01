<?php

namespace App\Livewire;

use App\Models\TableBooking;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MyBookingsPage extends Component
{
    use WithPagination;

    public function render()
    {
        $bookings = TableBooking::query()
            ->with('restaurant')
            ->where('user_id', auth()->id())
            ->latest('booked_at')
            ->paginate(10);

        return view('livewire.my-bookings-page', [
            'bookings' => $bookings,
        ]);
    }
}
