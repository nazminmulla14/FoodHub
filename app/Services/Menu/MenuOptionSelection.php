<?php

namespace App\Services\Menu;

use App\Models\MenuItem;
use App\Models\MenuOption;

class MenuOptionSelection
{
    /**
     * @param  list<int>  $optionIds
     * @return array{option_ids: list<int>, options_key: string, options_label: string, unit_price: float}
     */
    public static function resolve(MenuItem $item, array $optionIds): array
    {
        $optionIds = array_values(array_unique(array_map('intval', $optionIds)));
        sort($optionIds);

        $options = $optionIds === []
            ? collect()
            : MenuOption::query()->with('group')->whereIn('id', $optionIds)->get();

        if ($options->count() !== count($optionIds)) {
            throw new \RuntimeException('One of the selected options is no longer available.');
        }

        foreach ($options as $option) {
            if ((int) $option->group->menu_item_id !== (int) $item->id) {
                throw new \RuntimeException('One of the selected options does not belong to this dish.');
            }
        }

        $groups = $item->relationLoaded('optionGroups')
            ? $item->optionGroups
            : $item->optionGroups()->with('options')->get();

        foreach ($groups as $group) {
            $count = $options->where('menu_option_group_id', $group->id)->count();
            $min = $group->is_required ? max(1, (int) $group->min_select) : (int) $group->min_select;
            $max = max((int) $group->max_select, $min);

            if ($count < $min || $count > $max) {
                throw new \RuntimeException('Choose valid options for '.$group->name.'.');
            }
        }

        $delta = (float) $options->sum(fn (MenuOption $option) => (float) $option->price_delta);

        return [
            'option_ids' => $optionIds,
            'options_key' => implode('-', $optionIds),
            'options_label' => $options->pluck('name')->implode(', '),
            'unit_price' => round((float) $item->price + $delta, 2),
        ];
    }
}
