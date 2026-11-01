<?php

namespace App\Services\Menu;

use App\Models\MenuItem;

class SyncMenuOptions
{
    /**
     * @param  list<array{name?: string, is_required?: bool, min_select?: int, max_select?: int, options?: list<array{name?: string, price_delta?: mixed}>}>  $groups
     */
    public function sync(MenuItem $menuItem, array $groups): void
    {
        $menuItem->optionGroups()->delete();

        foreach ($groups as $group) {
            $name = trim((string) ($group['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $min = max(0, (int) ($group['min_select'] ?? 0));
            $max = max($min, (int) ($group['max_select'] ?? 1));

            $created = $menuItem->optionGroups()->create([
                'name' => $name,
                'is_required' => (bool) ($group['is_required'] ?? false),
                'min_select' => $min,
                'max_select' => $max,
            ]);

            foreach ($group['options'] ?? [] as $option) {
                $optionName = trim((string) ($option['name'] ?? ''));

                if ($optionName === '') {
                    continue;
                }

                $created->options()->create([
                    'name' => $optionName,
                    'price_delta' => $option['price_delta'] !== '' && $option['price_delta'] !== null
                        ? $option['price_delta']
                        : 0,
                ]);
            }
        }
    }
}
