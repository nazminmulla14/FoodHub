<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('FoodHub order #'.$this->order->id.' confirmed')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Thanks for your order at FoodHub!')
            ->line('Order total: ₹'.number_format((float) $this->order->total, 2))
            ->line('We will notify you when your order is being prepared.')
            ->action('View your orders', url('/orders'));
    }
}
