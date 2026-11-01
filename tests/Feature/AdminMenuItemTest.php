<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\FoodHubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMenuItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_menu_item_via_livewire(): void
    {
        $this->seed(FoodHubSeeder::class);

        $admin = User::factory()->admin()->create();
        $category = Category::query()->firstOrFail();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\MenuItemForm::class)
            ->set('name', 'Test Curry')
            ->set('description', 'Spicy and fresh')
            ->set('price', '149.50')
            ->set('category_id', $category->id)
            ->set('is_available', true)
            ->call('save')
            ->assertRedirect(route('admin.menu-items'));

        $this->assertDatabaseHas('menu_items', [
            'name' => 'Test Curry',
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_can_archive_and_restore_menu_item(): void
    {
        $this->seed(FoodHubSeeder::class);

        $admin = User::factory()->admin()->create();
        $item = MenuItem::query()->firstOrFail();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\MenuItemsIndex::class)
            ->call('archive', $item->id);

        $this->assertSoftDeleted('menu_items', ['id' => $item->id]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\MenuItemsIndex::class)
            ->set('showArchived', true)
            ->call('restore', $item->id);

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    }

    public function test_customer_cannot_access_menu_item_create_page(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($user)
            ->get(route('admin.menu-items.create'))
            ->assertForbidden();
    }
}
