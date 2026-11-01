<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 fw-semibold">Newsletter subscribers</h1>
        <a href="{{ route('admin.newsletter.export') }}" class="btn btn-outline-secondary btn-sm">Export CSV</a>
    </div>

    <div class="admin-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Subscribed</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscribers as $subscriber)
                    <tr wire:key="subscriber-{{ $subscriber->id }}">
                        <td>{{ $subscriber->email }}</td>
                        <td>{{ $subscriber->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">No subscribers yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subscribers->links() }}
</div>
