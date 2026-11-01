<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'restaurant_id',
        'starts_at',
        'ends_at',
        'usage_limit',
        'used_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function discountFor(float $subtotal): float
    {
        $amount = $this->type === CouponType::Percent
            ? round($subtotal * ((float) $this->value / 100), 2)
            : round((float) $this->value, 2);

        return min($amount, $subtotal);
    }

    public function isUsableFor(?int $restaurantId): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($this->restaurant_id && (int) $this->restaurant_id !== (int) $restaurantId) {
            return false;
        }

        return true;
    }

    public static function findByCode(string $code): ?self
    {
        $code = strtoupper(trim($code));

        if ($code === '') {
            return null;
        }

        return self::query()->whereRaw('upper(code) = ?', [$code])->first();
    }
}
