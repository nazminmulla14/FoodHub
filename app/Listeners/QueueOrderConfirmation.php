<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendOrderConfirmation;

class QueueOrderConfirmation
{
    public function handle(OrderPlaced $event): void
    {
        SendOrderConfirmation::dispatch($event->order);
    }
}
