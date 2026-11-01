<?php

namespace App\Livewire;

use App\Models\Restaurant;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RestaurantsListPage extends Component
{
    use WithPagination;

    public string $search = '';

    public string $cuisine = '';

    public bool $vegOnly = false;

    public string $minRating = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCuisine(): void
    {
        $this->resetPage();
    }

    public function updatedVegOnly(): void
    {
        $this->resetPage();
    }

    public function updatedMinRating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $restaurants = Restaurant::query()
            ->publiclyVisible()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('address', 'like', $term);
                });
            })
            ->when($this->cuisine !== '', fn ($q) => $q->whereHas('menuItems', fn ($items) => $items->where('cuisine', $this->cuisine)))
            ->when($this->vegOnly, fn ($q) => $q->whereHas('menuItems', fn ($items) => $items->where('is_veg', true)->where('is_available', true)))
            ->when($this->minRating !== '', fn ($q) => $q->having('reviews_avg_rating', '>=', (float) $this->minRating))
            ->orderBy('name')
            ->paginate(12);

        $cuisines = \App\Models\MenuItem::query()
            ->whereNotNull('cuisine')
            ->where('cuisine', '!=', '')
            ->distinct()
            ->orderBy('cuisine')
            ->pluck('cuisine');

        return view('livewire.restaurants-list-page', [
            'restaurants' => $restaurants,
            'cuisines' => $cuisines,
        ]);
    }
}
