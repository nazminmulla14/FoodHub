<?php

namespace App\Livewire;

use App\Jobs\SendContactMessageAlert;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ContactPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public ?string $successMessage = null;

    public function send(): void
    {
        $this->successMessage = null;

        $key = 'contact:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('message', 'Too many messages. Please try again later.');

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = ContactMessage::create($validated);
        SendContactMessageAlert::dispatch($message);
        RateLimiter::hit($key, 3600);

        $this->reset();
        $this->successMessage = 'Thank you! We received your message.';
    }

    public function render()
    {
        return view('livewire.contact-page');
    }
}
