<?php

namespace App\Livewire\Admin;

use App\Concerns\ScopesOwnedRestaurants;
use App\Models\Review;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ReviewsIndex extends Component
{
    use ScopesOwnedRestaurants, WithPagination;

    public string $reply = '';

    public ?int $replyingId = null;

    #[On('panel-restaurant-changed')]
    public function refreshForRestaurant(): void
    {
        $this->resetPage();
    }

    public function startReply(int $reviewId): void
    {
        $review = Review::query()->findOrFail($reviewId);
        $this->authorize('reply', $review);
        $this->replyingId = $review->id;
        $this->reply = (string) $review->owner_reply;
    }

    public function saveReply(): void
    {
        $review = Review::query()->findOrFail($this->replyingId);
        $this->authorize('reply', $review);

        $validated = $this->validate([
            'reply' => ['required', 'string', 'max:1000'],
        ]);

        $review->update(['owner_reply' => $validated['reply']]);
        $this->replyingId = null;
        $this->reply = '';
    }

    public function render()
    {
        $this->authorize('viewAny', Review::class);

        $reviews = $this->scopeOwnedRestaurants(
            Review::query()->with(['user', 'restaurant', 'order'])
        )->latest()->paginate(15);

        return view('livewire.admin.reviews-index', [
            'reviews' => $reviews,
        ]);
    }
}
