<div>
    <h1 class="h3 mb-4 fw-semibold">Reviews</h1>

    <div class="admin-table-wrap">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Restaurant</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Reply</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr wire:key="review-{{ $review->id }}">
                        <td>{{ $review->restaurant->name }}</td>
                        <td>{{ $review->rating }}/5</td>
                        <td>
                            <div>{{ $review->body }}</div>
                            <small class="text-muted">{{ $review->user->name }} · order #{{ $review->order_id }}</small>
                        </td>
                        <td style="min-width: 220px;">
                            @if ($replyingId === $review->id)
                                <textarea class="form-control form-control-sm mb-2" rows="2" wire:model="reply"></textarea>
                                @error('reply') <div class="text-danger small">{{ $message }}</div> @enderror
                                <button type="button" class="btn btn-sm btn-primary" wire:click="saveReply">Save</button>
                            @else
                                <div class="small mb-2">{{ $review->owner_reply ?: 'No reply yet.' }}</div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="startReply({{ $review->id }})">Reply</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No reviews yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $reviews->links() }}</div>
</div>
