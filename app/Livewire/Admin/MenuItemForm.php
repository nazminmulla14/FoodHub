<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\MenuItem;
use App\Services\Menu\SaveMenuItemService;
use App\Services\Menu\SyncMenuOptions;
use App\Support\AdminRestaurantContext;
use App\Support\Panel;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class MenuItemForm extends Component
{
    use WithFileUploads;

    public ?MenuItem $menuItem = null;

    public string $name = '';

    public string $description = '';

    public string $cuisine = '';

    public bool $is_veg = false;

    public string $price = '';

    public int|string $category_id = '';

    public bool $is_available = true;

    public $imageUpload = null;

    public array $optionGroups = [];

    public function mount(?MenuItem $menuItem = null): void
    {
        if ($menuItem?->exists) {
            $this->menuItem = $menuItem;
            $this->authorize('update', $this->menuItem);

            $this->name = $this->menuItem->name;
            $this->description = (string) $this->menuItem->description;
            $this->cuisine = (string) $this->menuItem->cuisine;
            $this->is_veg = (bool) $this->menuItem->is_veg;
            $this->price = (string) $this->menuItem->price;
            $this->category_id = $this->menuItem->category_id;
            $this->is_available = $this->menuItem->is_available;
            $this->optionGroups = $this->menuItem->optionGroups()->with('options')->get()->map(fn ($group) => [
                'name' => $group->name,
                'is_required' => $group->is_required,
                'min_select' => $group->min_select,
                'max_select' => $group->max_select,
                'options' => $group->options->map(fn ($option) => [
                    'name' => $option->name,
                    'price_delta' => (string) $option->price_delta,
                ])->all(),
            ])->all();

            return;
        }

        $this->menuItem = null;
        $this->authorize('create', MenuItem::class);
    }

    public function addOptionGroup(): void
    {
        $this->optionGroups[] = [
            'name' => '',
            'is_required' => false,
            'min_select' => 0,
            'max_select' => 1,
            'options' => [
                ['name' => '', 'price_delta' => '0'],
            ],
        ];
    }

    public function addOption(int $groupIndex): void
    {
        $this->optionGroups[$groupIndex]['options'][] = ['name' => '', 'price_delta' => '0'];
    }

    public function removeOptionGroup(int $groupIndex): void
    {
        unset($this->optionGroups[$groupIndex]);
        $this->optionGroups = array_values($this->optionGroups);
    }

    public function save(SaveMenuItemService $saveMenuItem, SyncMenuOptions $syncMenuOptions): void
    {
        if ($this->menuItem) {
            $this->authorize('update', $this->menuItem);
        } else {
            $this->authorize('create', MenuItem::class);
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cuisine' => ['nullable', 'string', 'max:80'],
            'is_veg' => ['boolean'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'is_available' => ['boolean'],
            'imageUpload' => ['nullable', 'image', 'max:2048'],
            'optionGroups' => ['array'],
            'optionGroups.*.name' => ['nullable', 'string', 'max:80'],
            'optionGroups.*.is_required' => ['boolean'],
            'optionGroups.*.min_select' => ['integer', 'min:0', 'max:10'],
            'optionGroups.*.max_select' => ['integer', 'min:1', 'max:10'],
            'optionGroups.*.options' => ['array'],
            'optionGroups.*.options.*.name' => ['nullable', 'string', 'max:80'],
            'optionGroups.*.options.*.price_delta' => ['nullable', 'numeric'],
        ]);

        $menuItem = $saveMenuItem->save(
            $validated,
            $this->imageUpload,
            $this->menuItem,
            auth()->user(),
        );

        $syncMenuOptions->sync($menuItem, $this->optionGroups);

        session()->flash('admin_success', $this->menuItem ? 'Menu item updated.' : 'Menu item created.');

        $this->redirect(Panel::route('menu-items'), navigate: true);
    }

    public function render(AdminRestaurantContext $context)
    {
        $restaurant = $context->selected();

        $categories = $restaurant
            ? Category::query()->where('restaurant_id', $restaurant->id)->orderBy('name')->get()
            : collect();

        return view('livewire.admin.menu-item-form', [
            'categories' => $categories,
        ]);
    }
}
