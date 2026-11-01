<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0 fw-semibold">Menu items</h1>
        <a href="{{ \App\Support\Panel::route('menu-items.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Add menu item
        </a>
    </div>

    @if (session('admin_success'))
        <div class="alert alert-success">{{ session('admin_success') }}</div>
    @endif

    <div class="admin-card mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <input type="search" class="form-control" placeholder="Search menu items..." wire:model.live.debounce.300ms="search">
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="showArchived" wire:model.live="showArchived">
                    <label class="form-check-label" for="showArchived">Show archived (soft deleted)</label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Available</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr wire:key="item-{{ $item->id }}">
                        <td>
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="" width="48" height="48" class="rounded" style="object-fit: cover;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-medium">{{ $item->name }}</td>
                        <td>{{ $item->category->name }}</td>
                        <td>₹{{ number_format((float) $item->price, 2) }}</td>
                        <td>
                            @if (! $showArchived)
                                <button type="button" class="btn btn-sm {{ $item->is_available ? 'btn-success' : 'btn-secondary' }}" wire:click="toggleAvailability({{ $item->id }})">
                                    {{ $item->is_available ? 'Yes' : 'No' }}
                                </button>
                            @else
                                <span class="text-muted">Archived</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($showArchived)
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="restore({{ $item->id }})">Restore</button>
                            @else
                                <a href="{{ \App\Support\Panel::route('menu-items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="archive({{ $item->id }})" wire:confirm="Archive this menu item?">Archive</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No menu items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $items->links() }}
    </div>
</div>
