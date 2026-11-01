<?php

namespace App\Livewire\Admin;

use App\Models\RestaurantHour;
use App\Support\AdminRestaurantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class RestaurantHours extends Component
{
    public array $days = [];

    public function mount(AdminRestaurantContext $context): void
    {
        $restaurant = $context->selected();
        abort_unless($restaurant, 404);

        $existing = $restaurant->hours()->get()->keyBy('weekday');
        $names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($names as $weekday => $name) {
            $row = $existing->get($weekday);
            $this->days[] = [
                'weekday' => $weekday,
                'name' => $name,
                'opens_at' => $row && $row->opens_at ? substr((string) $row->opens_at, 0, 5) : '09:00',
                'closes_at' => $row && $row->closes_at ? substr((string) $row->closes_at, 0, 5) : '22:00',
                'is_closed' => (bool) ($row->is_closed ?? false),
            ];
        }
    }

    public function save(AdminRestaurantContext $context): void
    {
        $restaurant = $context->selected();
        abort_unless($restaurant, 404);

        $this->validate([
            'days' => ['array', 'size:7'],
            'days.*.opens_at' => ['required', 'date_format:H:i'],
            'days.*.closes_at' => ['required', 'date_format:H:i'],
            'days.*.is_closed' => ['boolean'],
        ]);

        foreach ($this->days as $day) {
            RestaurantHour::query()->updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'weekday' => $day['weekday'],
                ],
                [
                    'opens_at' => $day['opens_at'],
                    'closes_at' => $day['closes_at'],
                    'is_closed' => (bool) $day['is_closed'],
                ]
            );
        }

        session()->flash('admin_success', 'Opening hours saved.');
    }

    public function render()
    {
        return view('livewire.admin.restaurant-hours');
    }
}
