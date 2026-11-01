<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Restaurants</h1>
    </div>

    @if (session('admin_success'))
        <div class="alert alert-success">{{ session('admin_success') }}</div>
    @endif

    <div class="mb-3" style="max-width: 240px;">
        <select class="form-select form-select-sm" wire:model.live="statusFilter">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}">{{ ucfirst($status->value) }}</option>
            @endforeach
        </select>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Owner</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($restaurants as $restaurant)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $restaurant->name }}</div>
                            <small class="text-muted">{{ $restaurant->slug }}</small>
                        </td>
                        <td>{{ $restaurant->owner?->email ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $restaurant->status->value }}</span></td>
                        <td class="d-flex flex-wrap gap-1">
                            @if ($restaurant->status !== \App\Enums\RestaurantStatus::Active)
                                <button type="button" class="btn btn-sm btn-success" wire:click="setStatus({{ $restaurant->id }}, 'active')">Approve</button>
                            @endif
                            @if ($restaurant->status !== \App\Enums\RestaurantStatus::Suspended)
                                <button type="button" class="btn btn-sm btn-outline-warning" wire:click="setStatus({{ $restaurant->id }}, 'suspended')">Suspend</button>
                            @endif
                            @if ($restaurant->status !== \App\Enums\RestaurantStatus::Pending)
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="setStatus({{ $restaurant->id }}, 'pending')">Pending</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $restaurants->links() }}
</div>
