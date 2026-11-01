<?php

namespace App\Services\Cart;

use App\Exceptions\CartRestaurantConflictException;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\Menu\MenuOptionSelection;
use App\Support\OrderPricing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'foodhub_guest_cart';

    /**
     * @param  list<int>  $optionIds
     */
    public function add(int $menuItemId, int $quantity = 1, bool $replaceIfDifferentRestaurant = false, array $optionIds = []): void
    {
        $quantity = max(1, $quantity);

        $menuItem = MenuItem::query()->with(['restaurant.hours', 'optionGroups.options'])->findOrFail($menuItemId);

        if (! $menuItem->is_available) {
            throw new \RuntimeException($menuItem->name.' is sold out.');
        }

        if (! $menuItem->restaurant->isOpen()) {
            throw new \RuntimeException($menuItem->restaurant->name.' is closed right now.');
        }

        $selection = MenuOptionSelection::resolve($menuItem, $optionIds);

        $this->assertSingleRestaurantCart($menuItem, $replaceIfDifferentRestaurant);

        if (Auth::check()) {
            $item = CartItem::firstOrNew([
                'user_id' => Auth::id(),
                'menu_item_id' => $menuItemId,
                'options_key' => $selection['options_key'],
            ]);
            $item->options = $selection['option_ids'];
            $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
            $item->save();

            return;
        }

        $lines = $this->guestLines();
        $found = false;

        foreach ($lines as &$line) {
            if ((int) $line['menu_item_id'] === $menuItemId && ($line['option_ids'] ?? []) == $selection['option_ids']) {
                $line['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        unset($line);

        if (! $found) {
            $lines[] = [
                'menu_item_id' => $menuItemId,
                'quantity' => $quantity,
                'option_ids' => $selection['option_ids'],
            ];
        }

        Session::put(self::SESSION_KEY, $lines);
    }

    public function updateQuantity(int $menuItemId, int $quantity, string $optionsKey = ''): void
    {
        if ($quantity < 1) {
            $this->remove($menuItemId, $optionsKey);

            return;
        }

        if (Auth::check()) {
            CartItem::query()
                ->where('user_id', Auth::id())
                ->where('menu_item_id', $menuItemId)
                ->where('options_key', $optionsKey)
                ->update(['quantity' => $quantity]);

            return;
        }

        $lines = $this->guestLines();

        foreach ($lines as &$line) {
            $key = implode('-', $line['option_ids'] ?? []);
            if ((int) $line['menu_item_id'] === $menuItemId && $key === $optionsKey) {
                $line['quantity'] = $quantity;
            }
        }
        unset($line);

        Session::put(self::SESSION_KEY, $lines);
    }

    public function remove(int $menuItemId, string $optionsKey = ''): void
    {
        if (Auth::check()) {
            CartItem::query()
                ->where('user_id', Auth::id())
                ->where('menu_item_id', $menuItemId)
                ->where('options_key', $optionsKey)
                ->delete();

            return;
        }

        $lines = array_values(array_filter($this->guestLines(), function (array $line) use ($menuItemId, $optionsKey) {
            $key = implode('-', $line['option_ids'] ?? []);

            return ! ((int) $line['menu_item_id'] === $menuItemId && $key === $optionsKey);
        }));

        Session::put(self::SESSION_KEY, $lines);
    }

    public function clear(): void
    {
        if (Auth::check()) {
            CartItem::query()->where('user_id', Auth::id())->delete();

            return;
        }

        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, array{menu_item: MenuItem, quantity: int, option_ids: list<int>, options_key: string, options_label: string, unit_price: float, line_total: float}>
     */
    public function lines(): Collection
    {
        $raw = $this->rawLines();

        if ($raw->isEmpty()) {
            return collect();
        }

        $items = MenuItem::query()
            ->with(['category', 'restaurant.hours', 'optionGroups.options'])
            ->available()
            ->whereIn('id', $raw->pluck('menu_item_id'))
            ->get()
            ->keyBy('id');

        return $raw->map(function (array $line) use ($items) {
            $menuItem = $items->get($line['menu_item_id']);

            if (! $menuItem) {
                return null;
            }

            try {
                $selection = MenuOptionSelection::resolve($menuItem, $line['option_ids']);
            } catch (\RuntimeException) {
                return null;
            }

            return [
                'menu_item' => $menuItem,
                'quantity' => $line['quantity'],
                'option_ids' => $selection['option_ids'],
                'options_key' => $selection['options_key'],
                'options_label' => $selection['options_label'],
                'unit_price' => $selection['unit_price'],
                'line_total' => round($selection['unit_price'] * $line['quantity'], 2),
            ];
        })->filter()->values();
    }

    public function itemCount(): int
    {
        return (int) $this->rawLines()->sum('quantity');
    }

    public function subtotal(): float
    {
        return (float) $this->lines()->sum('line_total');
    }

    /**
     * @return array{lines: Collection, subtotal: float, discount: float, tax: float, total: float}
     */
    public function summary(): array
    {
        $lines = $this->lines();
        $subtotal = (float) $lines->sum('line_total');
        $pricing = OrderPricing::totalsFromSubtotal($subtotal);

        return [
            'lines' => $lines,
            'subtotal' => $pricing['subtotal'],
            'discount' => $pricing['discount'],
            'tax' => $pricing['tax'],
            'total' => $pricing['total'],
        ];
    }

    public function restaurantIdInCart(): ?int
    {
        $ids = $this->restaurantIdsInCart();

        return $ids->count() === 1 ? (int) $ids->first() : null;
    }

    public function mergeGuestCartIntoUser(User $user): void
    {
        $guest = $this->guestLines();

        if ($guest === []) {
            return;
        }

        Session::forget(self::SESSION_KEY);

        foreach ($guest as $line) {
            try {
                $this->add((int) $line['menu_item_id'], (int) $line['quantity'], optionIds: $line['option_ids'] ?? []);
            } catch (\RuntimeException) {
                continue;
            }
        }
    }

    /**
     * @return Collection<int, array{menu_item_id: int, quantity: int, option_ids: list<int>}>
     */
    private function rawLines(): Collection
    {
        if (Auth::check()) {
            return CartItem::query()
                ->where('user_id', Auth::id())
                ->get()
                ->map(fn (CartItem $item) => [
                    'menu_item_id' => (int) $item->menu_item_id,
                    'quantity' => (int) $item->quantity,
                    'option_ids' => array_map('intval', $item->options ?? []),
                ]);
        }

        return collect($this->guestLines());
    }

    /**
     * @return list<array{menu_item_id: int, quantity: int, option_ids: list<int>}>
     */
    private function guestLines(): array
    {
        $raw = Session::get(self::SESSION_KEY, []);

        if ($raw === []) {
            return [];
        }

        $first = reset($raw);

        if (is_int($first)) {
            $lines = [];

            foreach ($raw as $id => $qty) {
                $lines[] = [
                    'menu_item_id' => (int) $id,
                    'quantity' => (int) $qty,
                    'option_ids' => [],
                ];
            }

            return $lines;
        }

        return array_values($raw);
    }

    /**
     * @return Collection<int, int>
     */
    private function restaurantIdsInCart(): Collection
    {
        $ids = $this->rawLines()->pluck('menu_item_id');

        if ($ids->isEmpty()) {
            return collect();
        }

        return MenuItem::query()
            ->whereIn('id', $ids)
            ->pluck('restaurant_id')
            ->unique()
            ->values();
    }

    private function assertSingleRestaurantCart(MenuItem $menuItem, bool $replaceIfDifferentRestaurant): void
    {
        $restaurantIds = $this->restaurantIdsInCart();

        if ($restaurantIds->isEmpty()) {
            return;
        }

        if ($restaurantIds->contains($menuItem->restaurant_id)) {
            return;
        }

        if ($replaceIfDifferentRestaurant) {
            $this->clear();

            return;
        }

        $currentRestaurant = MenuItem::query()
            ->with('restaurant')
            ->whereIn('id', $this->rawLines()->pluck('menu_item_id'))
            ->first()
            ?->restaurant;

        throw new CartRestaurantConflictException(
            $currentRestaurant?->name ?? 'another restaurant',
            $menuItem->restaurant?->name ?? 'this restaurant',
        );
    }
}
