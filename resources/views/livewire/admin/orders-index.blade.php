<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0 fw-semibold">Orders</h1>
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('admin.orders.export', ['status' => $statusFilter]) }}" class="btn btn-outline-secondary btn-sm">Export CSV</a>
        @endif
    </div>

    @if (session('admin_error'))
        <div class="alert alert-danger">{{ session('admin_error') }}</div>
    @endif

    <div class="admin-card mb-3" style="max-width: 280px;">
        <label class="form-label small text-muted">Filter by status</label>
        <select class="form-select form-select-sm" wire:model.live="statusFilter">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="admin-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr wire:key="order-{{ $order->id }}">
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td><span class="badge bg-secondary">{{ $order->status->label() }}</span></td>
                        <td>₹{{ number_format((float) $order->total, 2) }}</td>
                        <td>
                            @if ($order->status->next())
                                <button type="button" class="btn btn-sm btn-primary" wire:click="advance({{ $order->id }})">
                                    Mark {{ $order->status->next()->label() }}
                                </button>
                            @else
                                <span class="text-muted small">{{ $order->status->label() }}</span>
                            @endif
                            @if ($order->status->canBeCancelled())
                                <button type="button" class="btn btn-sm btn-outline-danger ms-1" wire:click="cancel({{ $order->id }})">Cancel</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
