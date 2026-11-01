<?php

namespace App\Livewire;

use App\Services\Cart\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartNavBadge extends Component
{
    public int $count = 0;

    public function mount(CartService $cart): void
    {
        $this->count = $cart->itemCount();
    }

    #[On('cart-updated')]
    public function refreshCount(CartService $cart): void
    {
        $this->count = $cart->itemCount();
    }

    public function render()
    {
        return view('livewire.cart-nav-badge');
    }
}
