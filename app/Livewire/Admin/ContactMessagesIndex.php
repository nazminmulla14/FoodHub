<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ContactMessagesIndex extends Component
{
    use WithPagination;

    public function markRead(int $messageId): void
    {
        $message = ContactMessage::query()->findOrFail($messageId);
        $this->authorize('update', $message);

        if ($message->read_at === null) {
            $message->read_at = now();
            $message->save();
        }
    }

    public function render()
    {
        $this->authorize('viewAny', ContactMessage::class);

        $messages = ContactMessage::query()
            ->latest()
            ->paginate(20);

        return view('livewire.admin.contact-messages-index', [
            'messages' => $messages,
        ]);
    }
}
