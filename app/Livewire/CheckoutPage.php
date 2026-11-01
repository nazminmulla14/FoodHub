<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\Orders\ConfirmOrderPaymentService;
use App\Services\Orders\InitiateOrderPaymentService;
use App\Support\OrderPricing;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CheckoutPage extends Component
{
    public string $notes = '';

    public ?int $addressId = null;

    public string $couponCode = '';

    public ?string $errorMessage = null;

    public ?array $checkout = null;

    public ?int $pendingOrderId = null;

    public function mount(CartService $cart): void
    {
        if ($cart->lines()->isEmpty()) {
            $this->redirect(route('cart'), navigate: true);
        }

        if (auth()->check()) {
            $this->addressId = auth()->user()->addresses()->where('is_default', true)->value('id')
                ?? auth()->user()->addresses()->value('id');
        }
    }

    public function startPayment(
        InitiateOrderPaymentService $initiate,
        ConfirmOrderPaymentService $confirm,
        CartService $cart,
    ): void {
        $this->errorMessage = null;
        $this->checkout = null;
        $this->pendingOrderId = null;

        if (! auth()->check()) {
            session()->put('url.intended', route('checkout'));
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $address = Address::query()->where('user_id', auth()->id())->find($this->addressId);

        if (! $address) {
            $this->errorMessage = 'Choose a delivery address. Add one on your profile if you do not have any yet.';

            return;
        }

        $result = $initiate->initiate(auth()->user(), $this->notes ?: null, $address->snapshot(), $this->couponCode ?: null);

        if (! ($result['success'] ?? false) || ! isset($result['order'], $result['checkout'])) {
            $this->errorMessage = $result['message'] ?? 'Could not start payment.';

            return;
        }

        $this->pendingOrderId = $result['order']->id;
        $this->checkout = $result['checkout'];

        if (! ($result['checkout']['mock'] ?? false)) {
            $this->dispatch('razorpay-checkout-ready');

            return;
        }

        if ($result['checkout']['mock'] ?? false) {
            $mockPaymentId = 'mock_pay_'.$result['order']->id;
            $ok = $confirm->confirm(
                $result['order'],
                $result['checkout']['order_id'],
                $mockPaymentId,
                'mock_sig',
                mock: true,
            );

            if (! $ok) {
                $this->errorMessage = 'Could not confirm payment.';

                return;
            }

            session()->flash('order_success', $result['order']->id);
            $this->dispatch('cart-updated');
            $this->redirect(route('orders.index'), navigate: true);
        }
    }

    public function confirmPayment(
        string $razorpayOrderId,
        string $razorpayPaymentId,
        string $razorpaySignature,
        ConfirmOrderPaymentService $confirm,
    ): void {
        $this->errorMessage = null;

        if (! $this->pendingOrderId) {
            $this->errorMessage = 'No pending order. Please try again.';

            return;
        }

        $order = Order::query()->where('id', $this->pendingOrderId)->where('user_id', auth()->id())->first();

        if (! $order) {
            $this->errorMessage = 'Order not found.';

            return;
        }

        $ok = $confirm->confirm($order, $razorpayOrderId, $razorpayPaymentId, $razorpaySignature, mock: false);

        if (! $ok) {
            $this->errorMessage = 'Payment verification failed.';

            return;
        }

        session()->flash('order_success', $order->id);
        $this->dispatch('cart-updated');
        $this->redirect(route('orders.index'), navigate: true);
    }

    public function openRazorpayModal(): void
    {
        if ($this->checkout && ! ($this->checkout['mock'] ?? true)) {
            $this->dispatch('razorpay-checkout-ready');
        }
    }

    public function render(CartService $cart)
    {
        $summary = $cart->summary();
        $restaurantId = $summary['lines']->first()['menu_item']->restaurant_id ?? null;
        $discount = 0.0;
        $coupon = $this->couponCode !== '' ? Coupon::findByCode($this->couponCode) : null;

        if ($coupon && $coupon->isUsableFor($restaurantId)) {
            $discount = $coupon->discountFor((float) $summary['subtotal']);
        }

        $pricing = OrderPricing::totalsFromSubtotal((float) $summary['subtotal'], $discount);

        return view('livewire.checkout-page', [
            'lines' => $summary['lines'],
            'subtotal' => $pricing['subtotal'],
            'discount' => $pricing['discount'],
            'tax' => $pricing['tax'],
            'total' => $pricing['total'],
            'couponApplied' => $discount > 0,
            'addresses' => auth()->check() ? auth()->user()->addresses()->orderByDesc('is_default')->get() : collect(),
            'razorpayEnabled' => app(\App\Services\Razorpay\RazorpayService::class)->isEnabled(),
        ]);
    }
}
