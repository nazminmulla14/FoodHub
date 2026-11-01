<?php

namespace App\Observers;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;

class MenuItemObserver
{
    public function saved(MenuItem $menuItem): void
    {
        $this->forgetMenuCache($menuItem);
    }

    public function deleted(MenuItem $menuItem): void
    {
        $this->forgetMenuCache($menuItem);
    }

    private function forgetMenuCache(MenuItem $menuItem): void
    {
        Cache::forget('menu_items.restaurant.'.$menuItem->restaurant_id);
    }
}
