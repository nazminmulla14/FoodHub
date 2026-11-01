<?php

namespace App\Jobs;

use App\Models\ContactMessage;
use App\Notifications\ContactMessageReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendContactMessageAlert implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $message) {}

    public function handle(): void
    {
        $inbox = config('foodhub.contact_inbox_email');

        if (! $inbox) {
            return;
        }

        Notification::route('mail', $inbox)
            ->notify(new ContactMessageReceivedNotification($this->message));
    }
}
