<?php

namespace App\Livewire\Admin;

use App\Concerns\ScopesOwnedRestaurants;
use App\Models\Category;
use App\Support\AdminRestaurantContext;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class CategoriesIndex extends Component
{
    use ScopesOwnedRestaurants, WithPagination;

    public string $name = '';

    public ?int $editingId = null;

    public string $editName = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Category::class);
    }

    #[On('panel-restaurant-changed')]
    public function refreshForRestaurant(): void
    {
        $this->resetPage();
        $this->cancelEdit();
    }

    public function save(AdminRestaurantContext $context): void
    {
        $this->authorize('create', Category::class);

        $restaurant = $context->selected();

        if (! $restaurant) {
            $this->addError('name', 'No restaurant selected.');

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($restaurant->id, $validated['name']),
        ]);

        $this->reset('name');
        $this->resetPage();
    }

    public function startEdit(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        $this->authorize('update', $category);

        $this->editingId = $category->id;
        $this->editName = $category->name;
    }

    public function updateCategory(): void
    {
        $category = Category::query()->findOrFail($this->editingId);
        $this->authorize('update', $category);

        $validated = $this->validate([
            'editName' => ['required', 'string', 'max:120'],
        ]);

        $category->update([
            'name' => $validated['editName'],
            'slug' => $this->uniqueSlug($category->restaurant_id, $validated['editName'], $category->id),
        ]);

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editName = '';
    }

    public function delete(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        $this->authorize('delete', $category);

        if ($category->menuItems()->exists()) {
            $this->addError('name', 'Cannot delete a category that still has menu items.');

            return;
        }

        $category->delete();
    }

    public function render()
    {
        $categories = $this->scopeOwnedRestaurants(
            Category::query()->withCount('menuItems')->orderBy('name')
        )->paginate(15);

        return view('livewire.admin.categories-index', [
            'categories' => $categories,
        ]);
    }

    private function uniqueSlug(int $restaurantId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (
            Category::query()
                ->where('restaurant_id', $restaurantId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

}
