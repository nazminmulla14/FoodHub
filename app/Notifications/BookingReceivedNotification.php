<?php

namespace App\Notifications;

use App\Models\TableBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public TableBooking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $restaurant = $this->booking->restaurant;

        return (new MailMessage)
            ->subject('FoodHub table booking request received')
            ->greeting('Hi '.$this->booking->guest_name.',')
            ->line('We received your table booking request at '.$restaurant->name.'.')
            ->line('When: '.$this->booking->booked_at->format('d M Y H:i'))
            ->line('Party size: '.$this->booking->party_size)
            ->line('Status: '.ucfirst(str_replace('_', ' ', $this->booking->status->value)))
            ->line('We will confirm your reservation by email soon.');
    }
}
