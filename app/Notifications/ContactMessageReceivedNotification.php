<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public ContactMessage $message) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New FoodHub contact message')
            ->greeting('New contact form submission')
            ->line('From: '.$this->message->name.' ('.$this->message->email.')')
            ->when($this->message->subject, fn (MailMessage $mail) => $mail->line('Subject: '.$this->message->subject))
            ->line($this->message->message)
            ->action('Open admin inbox', url('/admin/contact-messages'));
    }
}
