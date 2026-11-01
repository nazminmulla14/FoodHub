<?php

namespace App\Support;

class OrderPricing
{
    public static function taxRate(): float
    {
        return (float) config('foodhub.tax_rate', 0.05);
    }

    public static function taxFromSubtotal(float $subtotal): float
    {
        return round($subtotal * self::taxRate(), 2);
    }

    /**
     * @return array{subtotal: float, discount: float, tax: float, total: float}
     */
    public static function totalsFromSubtotal(float $subtotal, float $discount = 0): array
    {
        $subtotal = round($subtotal, 2);
        $discount = round(min(max($discount, 0), $subtotal), 2);
        $taxable = round($subtotal - $discount, 2);
        $tax = self::taxFromSubtotal($taxable);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => round($taxable + $tax, 2),
        ];
    }
}
