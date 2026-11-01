<div>
    <h1 class="h3 mb-4 fw-semibold">Coupons</h1>

    @if (session('admin_success'))
        <div class="alert alert-success">{{ session('admin_success') }}</div>
    @endif

    <form wire:submit="save" class="admin-card mb-4" style="max-width: 640px;">
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Code</label>
                <input class="form-control" wire:model="code">
                @error('code') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select class="form-select" wire:model="type">
                    <option value="percent">Percent</option>
                    <option value="flat">Flat ₹</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Value</label>
                <input type="number" step="0.01" class="form-control" wire:model="value">
                @error('value') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Restaurant</label>
                <select class="form-select" wire:model="restaurant_id">
                    <option value="">All restaurants</option>
                    @foreach ($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Starts</label>
                <input type="datetime-local" class="form-control" wire:model="starts_at">
            </div>
            <div class="col-md-3">
                <label class="form-label">Ends</label>
                <input type="datetime-local" class="form-control" wire:model="ends_at">
            </div>
            <div class="col-md-4">
                <label class="form-label">Usage limit</label>
                <input type="number" min="1" class="form-control" wire:model="usage_limit">
            </div>
            <div class="col-md-4 form-check mt-4">
                <input type="checkbox" class="form-check-input" id="coupon-active" wire:model="is_active">
                <label class="form-check-label" for="coupon-active">Active</label>
            </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Create coupon</button>
    </form>

    <div class="admin-table-wrap">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Restaurant</th>
                    <th>Used</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coupons as $coupon)
                    <tr wire:key="coupon-{{ $coupon->id }}">
                        <td>{{ $coupon->code }}</td>
                        <td>{{ $coupon->type->value === 'percent' ? $coupon->value.'%' : '₹'.$coupon->value }}</td>
                        <td>{{ $coupon->restaurant->name ?? 'All' }}</td>
                        <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggle({{ $coupon->id }})">
                                {{ $coupon->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $coupons->links() }}</div>
</div>
