<?php

namespace App\Jobs;

use App\Models\TableBooking;
use App\Notifications\BookingReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendBookingConfirmation implements ShouldQueue
{
    use Queueable;

    public function __construct(public TableBooking $booking) {}

    public function handle(): void
    {
        $this->booking->loadMissing('restaurant');

        Notification::route('mail', $this->booking->guest_email)
            ->notify(new BookingReceivedNotification($this->booking));
    }
}
