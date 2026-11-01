<div>
    <h1 class="h3 mb-4 fw-semibold">Contact messages</h1>

    <div class="admin-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr wire:key="contact-{{ $message->id }}" @class(['table-light' => $message->read_at === null])>
                        <td>
                            <div class="fw-medium">{{ $message->name }}</div>
                            <small class="text-muted">{{ $message->email }}</small>
                        </td>
                        <td>{{ $message->subject ?: '—' }}</td>
                        <td style="max-width: 320px;">
                            <span class="d-inline-block text-truncate w-100" title="{{ $message->message }}">
                                {{ \Illuminate\Support\Str::limit($message->message, 80) }}
                            </span>
                        </td>
                        <td>{{ $message->created_at->format('d M Y H:i') }}</td>
                        <td>
                            @if ($message->read_at === null)
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="markRead({{ $message->id }})">
                                    Mark read
                                </button>
                            @else
                                <span class="text-muted small">Read</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No messages yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $messages->links() }}
</div>
