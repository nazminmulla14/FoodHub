<?php

namespace App\Livewire;

use App\Models\MenuItem;
use App\Models\OrderItem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class HomePage extends Component
{
    public function render()
    {
        $popularItems = MenuItem::query()
            ->available()
            ->select('menu_items.*')
            ->selectSub(
                OrderItem::query()
                    ->selectRaw('coalesce(sum(quantity), 0)')
                    ->whereColumn('order_items.menu_item_id', 'menu_items.id'),
                'units_sold'
            )
            ->whereHas('restaurant', fn ($q) => $q->publiclyVisible())
            ->with('restaurant:id,name')
            ->orderByDesc('units_sold')
            ->orderByDesc('menu_items.created_at')
            ->limit(8)
            ->get();

        return view('livewire.home-page', [
            'popularItems' => $popularItems,
            'team' => config('foodhub.team'),
            'about' => config('foodhub.about'),
        ]);
    }
}
