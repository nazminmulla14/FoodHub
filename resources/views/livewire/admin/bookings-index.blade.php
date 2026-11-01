<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 fw-semibold">Table bookings</h1>
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('admin.bookings.export') }}" class="btn btn-outline-secondary btn-sm">Export CSV</a>
        @endif
    </div>

    <div class="admin-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>When</th>
                    <th>Party</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr wire:key="booking-{{ $booking->id }}">
                        <td>
                            <div class="fw-medium">{{ $booking->guest_name }}</div>
                            <small class="text-muted">{{ $booking->guest_email }}</small>
                        </td>
                        <td>{{ $booking->booked_at->format('d M Y H:i') }}</td>
                        <td>{{ $booking->party_size }}</td>
                        <td><span class="badge bg-secondary">{{ $booking->status->value }}</span></td>
                        <td>
                            <select class="form-select form-select-sm" style="min-width: 140px;" wire:change="updateStatus({{ $booking->id }}, $event.target.value)">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" @selected($booking->status === $status)>{{ $status->value }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No bookings yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $bookings->links() }}</div>
</div>
