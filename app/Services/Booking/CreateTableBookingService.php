<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Restaurant;
use App\Models\TableBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateTableBookingService
{
    public function create(
        Restaurant $restaurant,
        string $guestName,
        string $guestEmail,
        ?string $guestPhone,
        int $partySize,
        Carbon $bookedAt,
        ?string $specialRequest = null,
        ?User $user = null,
    ): TableBooking {
        return DB::transaction(function () use (
            $restaurant,
            $guestName,
            $guestEmail,
            $guestPhone,
            $partySize,
            $bookedAt,
            $specialRequest,
            $user
        ) {
            $slotStart = $bookedAt->copy()->startOfHour();
            $slotEnd = $slotStart->copy()->addHour();

            $existing = TableBooking::query()
                ->where('restaurant_id', $restaurant->id)
                ->where('status', '!=', BookingStatus::Cancelled)
                ->whereBetween('booked_at', [$slotStart, $slotEnd->copy()->subSecond()])
                ->count();

            if ($existing >= 5) {
                throw new \RuntimeException('This time slot is fully booked. Please choose another time.');
            }

            return TableBooking::create([
                'restaurant_id' => $restaurant->id,
                'user_id' => $user?->id,
                'guest_name' => $guestName,
                'guest_email' => $guestEmail,
                'guest_phone' => $guestPhone,
                'party_size' => $partySize,
                'booked_at' => $bookedAt,
                'status' => BookingStatus::Pending,
                'special_request' => $specialRequest,
            ]);
        });
    }
}
