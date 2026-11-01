<?php

namespace App\Livewire\Admin;

use App\Models\NewsletterSubscriber;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class NewsletterSubscribersIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $this->authorize('viewAny', NewsletterSubscriber::class);

        $subscribers = NewsletterSubscriber::query()
            ->latest()
            ->paginate(25);

        return view('livewire.admin.newsletter-subscribers-index', [
            'subscribers' => $subscribers,
        ]);
    }
}
