<?php

namespace App\Livewire;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Livewire\Component;

class RestaurantSearch extends Component
{
    public string $search = '';

    public function render()
    {
        $restaurants = [];

        $dishes = [];

        if (strlen(trim($this->search)) >= 2) {
            $term = '%'.trim($this->search).'%';

            $restaurants = Restaurant::query()
                ->publiclyVisible()
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhereHas('menuItems', fn ($items) => $items->where('name', 'like', $term));
                })
                ->take(5)
                ->get();

            $dishes = MenuItem::query()
                ->with('restaurant')
                ->available()
                ->where('name', 'like', $term)
                ->whereHas('restaurant', fn ($query) => $query->publiclyVisible())
                ->take(5)
                ->get();
        }

        return view('livewire.restaurant-search', [
            'restaurants' => $restaurants,
            'dishes' => $dishes,
        ]);
    }
}
