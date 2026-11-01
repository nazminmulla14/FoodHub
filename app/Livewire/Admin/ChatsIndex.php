<?php

namespace App\Livewire\Admin;

use App\Concerns\ScopesOwnedRestaurants;
use App\Models\Conversation;
use App\Services\Chat\OrderChat;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ChatsIndex extends Component
{
    use ScopesOwnedRestaurants, WithPagination;

    #[Locked]
    public bool $readOnly = true;

    public ?int $selectedId = null;

    public string $body = '';

    #[On('panel-restaurant-changed')]
    public function refreshForRestaurant(): void
    {
        $this->selectedId = null;
        $this->resetPage();
    }

    public function select(int $conversationId): void
    {
        $conversation = $this->scopedConversations()->findOrFail($conversationId);
        $this->authorize('view', $conversation);
        $this->selectedId = $conversation->id;
        $this->reset('body');
    }

    public function send(OrderChat $chat): void
    {
        abort_if($this->readOnly || $this->selectedId === null, 403);

        $conversation = $this->scopedConversations()->with('order')->findOrFail($this->selectedId);
        $this->authorize('message', $conversation->order);

        $validated = $this->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $chat->send($conversation->order, auth()->user(), $validated['body']);
        $this->reset('body');
    }

    public function render(OrderChat $chat)
    {
        $this->authorize('viewAny', Conversation::class);

        if ($this->selectedId && ! $this->readOnly) {
            $open = $this->scopedConversations()->find($this->selectedId);

            if ($open) {
                $chat->markRead($open, auth()->user());
            } else {
                $this->selectedId = null;
            }
        }

        $conversations = $this->scopedConversations()
            ->with(['customer', 'restaurant', 'order'])
            ->withCount([
                'messages as unread_count' => function ($messages) {
                    $messages->where('sender_id', '!=', auth()->id())
                        ->whereNull('read_at');
                },
            ])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $selected = null;

        if ($this->selectedId) {
            $selected = $this->scopedConversations()
                ->with(['messages.sender', 'customer', 'restaurant', 'order'])
                ->find($this->selectedId);

            if (! $selected) {
                $this->selectedId = null;
            }
        }

        return view('livewire.admin.chats-index', [
            'conversations' => $conversations,
            'selected' => $selected,
        ]);
    }

    protected function scopedConversations()
    {
        return $this->scopeOwnedRestaurants(Conversation::query());
    }
}
