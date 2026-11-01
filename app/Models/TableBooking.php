<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableBooking extends Model
{
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'party_size',
        'booked_at',
        'status',
        'special_request',
    ];

    protected function casts(): array
    {
        return [
            'booked_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
