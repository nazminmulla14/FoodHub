<?php

namespace App\Livewire;

use App\Services\Cart\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CartPage extends Component
{
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        //
    }

    public function updateQuantity(int $menuItemId, int $quantity, string $optionsKey, CartService $cart): void
    {
        $cart->updateQuantity($menuItemId, $quantity, $optionsKey);
        $this->dispatch('cart-updated');
    }

    public function remove(int $menuItemId, string $optionsKey, CartService $cart): void
    {
        $cart->remove($menuItemId, $optionsKey);
        $this->dispatch('cart-updated');
    }

    public function render(CartService $cart)
    {
        $summary = $cart->summary();

        return view('livewire.cart-page', [
            'lines' => $summary['lines'],
            'subtotal' => $summary['subtotal'],
            'restaurantName' => $summary['lines']->first()['menu_item']->restaurant->name ?? null,
        ]);
    }
}
