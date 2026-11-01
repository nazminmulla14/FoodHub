<?php

namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class NewsletterSignup extends Component
{
    public string $email = '';

    public ?string $successMessage = null;

    public function subscribe(): void
    {
        $this->successMessage = null;

        $key = 'newsletter:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many attempts. Please try again later.');

            return;
        }

        $validated = $this->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        NewsletterSubscriber::query()->firstOrCreate(
            ['email' => $validated['email']],
        );

        RateLimiter::hit($key, 3600);

        $this->reset('email');
        $this->successMessage = 'Thanks for subscribing!';
    }

    public function render()
    {
        return view('livewire.newsletter-signup');
    }
}
