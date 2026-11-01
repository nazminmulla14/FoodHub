<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestaurantStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Restaurant $restaurant) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->restaurant->status->value;

        return (new MailMessage)
            ->subject('FoodHub restaurant update: '.$this->restaurant->name)
            ->greeting('Hi '.$notifiable->name.',')
            ->line($this->restaurant->name.' is now '.$status.'.')
            ->line($status === 'active'
                ? 'Customers can see your menu and place orders.'
                : 'The restaurant is hidden from the public site until it is active again.');
    }
}
