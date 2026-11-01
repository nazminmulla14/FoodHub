<?php

namespace App\Livewire\Admin;

use App\Concerns\ScopesOwnedRestaurants;
use App\Models\MenuItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class MenuItemsIndex extends Component
{
    use ScopesOwnedRestaurants, WithPagination;

    public string $search = '';

    public bool $showArchived = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedShowArchived(): void
    {
        $this->resetPage();
    }

    #[On('panel-restaurant-changed')]
    public function refreshForRestaurant(): void
    {
        $this->resetPage();
    }

    public function toggleAvailability(int $menuItemId): void
    {
        $item = MenuItem::query()->findOrFail($menuItemId);
        $this->authorize('update', $item);

        $item->is_available = ! $item->is_available;
        $item->save();
    }

    public function archive(int $menuItemId): void
    {
        $item = MenuItem::query()->findOrFail($menuItemId);
        $this->authorize('delete', $item);

        $item->delete();
    }

    public function restore(int $menuItemId): void
    {
        $item = MenuItem::withTrashed()->findOrFail($menuItemId);
        $this->authorize('update', $item);

        $item->restore();
    }

    public function render()
    {
        $query = $this->scopeOwnedRestaurants(
            MenuItem::query()->with(['category', 'restaurant'])
        );

        if ($this->showArchived) {
            $query->onlyTrashed();
        }

        $items = $query
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.admin.menu-items-index', [
            'items' => $items,
        ]);
    }
}
