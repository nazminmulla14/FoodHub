<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Support\OrderPricing;
use Illuminate\Support\Facades\DB;

class CreatePendingOrderService
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    public function create(User $user, ?string $notes = null, ?string $deliveryAddress = null, ?string $couponCode = null): PlaceOrderResult
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return new PlaceOrderResult(false, message: 'Your cart is empty.');
        }

        $restaurantIds = $lines->pluck('menu_item.restaurant_id')->unique();
        if ($restaurantIds->count() > 1) {
            return new PlaceOrderResult(false, message: 'Please order from one restaurant at a time.');
        }

        $restaurantId = (int) $restaurantIds->first();
        $restaurant = Restaurant::query()->with('hours')->find($restaurantId);

        if (! $restaurant || ! $restaurant->isOpen()) {
            return new PlaceOrderResult(false, message: ($restaurant?->name ?? 'This restaurant').' is closed right now.');
        }

        try {
            $order = DB::transaction(function () use ($user, $lines, $restaurantId, $notes, $deliveryAddress, $couponCode) {
                $menuItems = MenuItem::query()
                    ->whereIn('id', $lines->pluck('menu_item.id'))
                    ->get()
                    ->keyBy('id');

                $subtotal = 0.0;

                foreach ($lines as $line) {
                    $menuItem = $menuItems->get($line['menu_item']->id);

                    if (! $menuItem || ! $menuItem->is_available) {
                        throw new \RuntimeException("{$line['menu_item']->name} is no longer available.");
                    }

                    $subtotal += (float) $line['unit_price'] * $line['quantity'];
                }

                $coupon = null;
                $discount = 0.0;

                if ($couponCode) {
                    $coupon = Coupon::findByCode($couponCode);

                    if (! $coupon || ! $coupon->isUsableFor($restaurantId)) {
                        throw new \RuntimeException('That promo code cannot be used on this order.');
                    }

                    $discount = $coupon->discountFor($subtotal);
                }

                $pricing = OrderPricing::totalsFromSubtotal($subtotal, $discount);

                $order = Order::create([
                    'user_id' => $user->id,
                    'restaurant_id' => $restaurantId,
                    'status' => OrderStatus::Pending,
                    'payment_status' => PaymentStatus::Pending,
                    'subtotal' => $pricing['subtotal'],
                    'discount' => $pricing['discount'],
                    'tax' => $pricing['tax'],
                    'total' => $pricing['total'],
                    'notes' => $notes,
                    'delivery_address' => $deliveryAddress,
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                ]);

                foreach ($lines as $line) {
                    $menuItem = $menuItems->get($line['menu_item']->id);

                    $order->items()->create([
                        'menu_item_id' => $menuItem->id,
                        'name' => $menuItem->name,
                        'options_label' => $line['options_label'] ?: null,
                        'option_ids' => $line['option_ids'],
                        'price' => $line['unit_price'],
                        'quantity' => $line['quantity'],
                    ]);
                }

                return $order->load('items', 'restaurant');
            });
        } catch (\RuntimeException $e) {
            return new PlaceOrderResult(false, message: $e->getMessage());
        }

        return new PlaceOrderResult(true, order: $order);
    }
}
