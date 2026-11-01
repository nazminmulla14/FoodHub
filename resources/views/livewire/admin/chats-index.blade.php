<div wire:poll.5s>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 fw-semibold">Order chats</h1>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="admin-table-wrap">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>With</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conversations as $conversation)
                            <tr wire:key="conversation-{{ $conversation->id }}" role="button" wire:click="select({{ $conversation->id }})" @class(['table-active' => $selectedId === $conversation->id])>
                                <td>
                                    <div>#{{ $conversation->order_id }}</div>
                                    <div class="small text-muted">{{ $conversation->restaurant->name }}</div>
                                </td>
                                <td>{{ $conversation->customer->name }}</td>
                                <td class="text-end">
                                    @if ($conversation->unread_count > 0)
                                        <span class="badge bg-danger">{{ $conversation->unread_count }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No order chats yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $conversations->links() }}</div>
        </div>

        <div class="col-lg-8">
            <div class="admin-card">
                @if ($selected)
                    <h2 class="h5">Order #{{ $selected->order_id }} · {{ $selected->customer->name }}</h2>
                    <p class="small text-muted">{{ $selected->restaurant->name }}</p>

                    <div class="border rounded p-3 mb-3 bg-light" style="height: 420px; overflow-y: auto;"
                         x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                         x-effect="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">
                        @forelse ($selected->messages as $message)
                            <div class="mb-3 {{ $message->sender_id === auth()->id() ? 'text-end' : '' }}" wire:key="inbox-message-{{ $message->id }}">
                                <div class="small text-muted">{{ $message->sender->name }} · {{ $message->created_at->format('d M H:i') }}</div>
                                <div class="d-inline-block text-start border rounded px-3 py-2 bg-white" style="max-width: 80%;">{{ $message->body }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No messages in this thread.</p>
                        @endforelse
                    </div>

                    @unless ($readOnly)
                        <form wire:submit="send">
                            <label class="form-label" for="owner-chat-body">Reply</label>
                            <textarea id="owner-chat-body" class="form-control mb-2" rows="3" wire:model="body" maxlength="1000"></textarea>
                            @error('body') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                            <button type="submit" class="btn btn-primary btn-sm">Send</button>
                        </form>
                    @endunless
                @else
                    <p class="text-muted mb-0">Select a conversation to read it.</p>
                @endif
            </div>
        </div>
    </div>
</div>
