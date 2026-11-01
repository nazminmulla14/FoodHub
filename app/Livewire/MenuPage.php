<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MenuPage extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryId = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $restaurant = Restaurant::query()->where('slug', 'foodhub-kitchen')->first();

        $categories = $restaurant
            ? Category::query()->where('restaurant_id', $restaurant->id)->orderBy('name')->get()
            : collect();

        $menuItems = MenuItem::query()
            ->with(['restaurant', 'category'])
            ->available()
            ->when($restaurant, fn ($q) => $q->forRestaurant($restaurant->id))
            ->whereHas('restaurant', fn ($q) => $q->where('is_active', true))
            ->when($this->categoryId !== '', fn ($q) => $q->where('category_id', $this->categoryId))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(8);

        return view('livewire.menu-page', [
            'menuItems' => $menuItems,
            'categories' => $categories,
        ]);
    }
}
