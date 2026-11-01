<div class="container-xxl py-5">
    <div class="container">
        <h1 class="mb-4">My table bookings</h1>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Restaurant</th>
                        <th>When</th>
                        <th>Party</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr wire:key="booking-{{ $booking->id }}">
                            <td>{{ $booking->restaurant->name }}</td>
                            <td>{{ $booking->booked_at->format('d M Y H:i') }}</td>
                            <td>{{ $booking->party_size }}</td>
                            <td>
                                <span class="badge text-bg-secondary">{{ str_replace('_', ' ', ucfirst($booking->status->value)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No bookings yet. <a href="{{ route('booking') }}">Book a table</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $bookings->links() }}
    </div>
</div>
