<?php

namespace App\Livewire\Admin;

use App\Support\AdminRestaurantContext;
use Livewire\Component;

class RestaurantSelector extends Component
{
    public int|string $restaurantId = '';

    public function mount(AdminRestaurantContext $context): void
    {
        $this->restaurantId = (string) ($context->selectedId() ?? '');
    }

    public function updatedRestaurantId(AdminRestaurantContext $context): void
    {
        if ($this->restaurantId === '') {
            return;
        }

        $context->setSelected((int) $this->restaurantId);
        $this->dispatch('panel-restaurant-changed');
    }

    public function render(AdminRestaurantContext $context)
    {
        $restaurants = $context->manageableRestaurants();

        return view('livewire.admin.restaurant-selector', [
            'restaurants' => $restaurants,
            'showSelector' => $restaurants->count() > 1,
        ]);
    }
}
