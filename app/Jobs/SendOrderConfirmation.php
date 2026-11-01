<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOrderConfirmation implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $this->order->loadMissing('user');
        $this->order->user->notify(new OrderPlacedNotification($this->order));
    }
}
