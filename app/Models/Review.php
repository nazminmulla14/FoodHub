<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'restaurant_id',
        'rating',
        'body',
        'owner_reply',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public static function canReview(Order $order, User $user): bool
    {
        return $order->user_id === $user->id
            && $order->status === OrderStatus::Completed
            && ! $order->review()->exists();
    }
}
