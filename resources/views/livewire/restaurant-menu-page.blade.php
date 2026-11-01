<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h5 class="section-title text-primary">Menu</h5>
            <h1>{{ $restaurant->name }}</h1>
            @if ($restaurant->address)
                <p class="text-muted">{{ $restaurant->address }}</p>
            @endif
            @if ($restaurant->reviews_count)
                <p class="mb-1">{{ number_format((float) $restaurant->reviews_avg_rating, 1) }} / 5 from {{ $restaurant->reviews_count }} review(s)</p>
            @endif
            @if (! $restaurant->isOpen())
                <div class="alert alert-warning">
                    Closed{{ $restaurant->nextOpenLabel() ? ', opens at '.$restaurant->nextOpenLabel() : '' }}. Ordering is unavailable until the kitchen opens. Table bookings are still open.
                </div>
            @endif
            <a href="{{ route('restaurants.index') }}" class="btn btn-link btn-sm">← All restaurants</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <input
                    type="search"
                    class="form-control"
                    placeholder="Search menu..."
                    wire:model.live.debounce.300ms="search"
                >
            </div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="categoryId">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="diet">
                    <option value="">Veg and non-veg</option>
                    <option value="veg">Veg only</option>
                    <option value="nonveg">Non-veg only</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" min="0" class="form-control" placeholder="Max price" wire:model.live.debounce.300ms="maxPrice">
            </div>
        </div>

        <div class="row g-4">
            @forelse ($menuItems as $item)
                <x-food-card :item="$item" />
            @empty
                <div class="col-12 text-center">
                    <p>No food items match your filters.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $menuItems->links() }}
        </div>
    </div>
</div>
