<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\Menu\MenuOptionSelection;

class ReorderService
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    /**
     * @return array{added: int, skipped: list<string>}
     */
    public function reorder(Order $order): array
    {
        $order->loadMissing('items.menuItem.optionGroups.options');

        $this->cart->clear();

        $skipped = [];
        $added = 0;

        foreach ($order->items as $item) {
            $menuItem = $item->menuItem;

            if (! $menuItem || ! $menuItem->is_available) {
                $skipped[] = $item->name;

                continue;
            }

            $optionIds = array_map('intval', $item->option_ids ?? []);

            try {
                MenuOptionSelection::resolve($menuItem, $optionIds);
            } catch (\RuntimeException) {
                $optionIds = [];
            }

            $this->cart->add($menuItem->id, (int) $item->quantity, optionIds: $optionIds);
            $added++;
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
        ];
    }
}
