<?php

namespace App\Livewire;

use App\Exceptions\CartRestaurantConflictException;
use App\Models\MenuItem;
use App\Services\Cart\CartService;
use Livewire\Component;

class AddToCart extends Component
{
    public int $menuItemId;

    public bool $disabled = false;

    public array $selectedOptionIds = [];

    public bool $showReplacePrompt = false;

    public ?string $replaceMessage = null;

    public ?string $error = null;

    public function addToCart(CartService $cart): void
    {
        $this->showReplacePrompt = false;
        $this->replaceMessage = null;
        $this->error = null;

        try {
            $cart->add($this->menuItemId, optionIds: $this->optionIds());
            $this->dispatch('cart-updated');
        } catch (CartRestaurantConflictException $e) {
            $this->replaceMessage = $e->getMessage();
            $this->showReplacePrompt = true;
        } catch (\RuntimeException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function replaceCartAndAdd(CartService $cart): void
    {
        $this->error = null;

        try {
            $cart->add($this->menuItemId, replaceIfDifferentRestaurant: true, optionIds: $this->optionIds());
            $this->showReplacePrompt = false;
            $this->replaceMessage = null;
            $this->dispatch('cart-updated');
        } catch (\RuntimeException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function cancelReplace(): void
    {
        $this->showReplacePrompt = false;
        $this->replaceMessage = null;
    }

    /**
     * @return list<int>
     */
    private function optionIds(): array
    {
        $ids = [];

        foreach ($this->selectedOptionIds as $value) {
            $values = is_array($value) ? $value : [$value];

            foreach ($values as $id) {
                if ($id !== '' && $id !== null) {
                    $ids[] = (int) $id;
                }
            }
        }

        return $ids;
    }

    public function render()
    {
        $item = MenuItem::query()->with('optionGroups.options')->find($this->menuItemId);

        return view('livewire.add-to-cart', [
            'optionGroups' => $item?->optionGroups ?? collect(),
        ]);
    }
}
