<div class="container-xxl py-5">
    <div class="container" style="max-width: 760px;">
        <a href="{{ route('orders.index') }}" class="small text-decoration-none">Back to orders</a>
        <h1 class="h3 mt-2 mb-1">Message {{ $order->restaurant->name }}</h1>
        <p class="text-muted mb-4">Order #{{ $order->id }}</p>

        <div class="border rounded p-3 mb-3 bg-light" style="height: 420px; overflow-y: auto;" wire:poll.5s
             x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
             x-effect="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">
            @forelse ($conversation?->messages ?? [] as $message)
                <div class="mb-3 {{ $message->sender_id === auth()->id() ? 'text-end' : '' }}" wire:key="message-{{ $message->id }}">
                    <div class="small text-muted">{{ $message->sender->name }} · {{ $message->created_at->format('d M H:i') }}</div>
                    <div class="d-inline-block text-start border rounded px-3 py-2 bg-white" style="max-width: 80%;">{{ $message->body }}</div>
                </div>
            @empty
                <p class="text-muted mb-0">No messages yet. Send a note about this order.</p>
            @endforelse
        </div>

        <form wire:submit="send">
            <label class="form-label" for="chat-body">Message</label>
            <textarea id="chat-body" class="form-control mb-2" rows="3" wire:model="body" maxlength="1000" placeholder="Ask about your order"></textarea>
            @error('body') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </div>
</div>
