<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RestaurantMenuPage extends Component
{
    use WithPagination;

    public Restaurant $restaurant;

    public string $search = '';

    public string $categoryId = '';

    public string $diet = '';

    public string $maxPrice = '';

    public function mount(string $slug): void
    {
        $this->restaurant = Restaurant::query()
            ->with(['hours', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $slug)
            ->publiclyVisible()
            ->firstOrFail();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedDiet(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::query()
            ->where('restaurant_id', $this->restaurant->id)
            ->orderBy('name')
            ->get();

        $menuItems = MenuItem::query()
            ->with(['restaurant.hours', 'category', 'optionGroups.options'])
            ->forRestaurant($this->restaurant->id)
            ->when($this->categoryId !== '', fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->diet === 'veg', fn ($q) => $q->where('is_veg', true))
            ->when($this->diet === 'nonveg', fn ($q) => $q->where('is_veg', false))
            ->when($this->maxPrice !== '' && is_numeric($this->maxPrice), fn ($q) => $q->where('price', '<=', $this->maxPrice))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(8);

        return view('livewire.restaurant-menu-page', [
            'menuItems' => $menuItems,
            'categories' => $categories,
        ]);
    }
}
