<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h5 class="section-title text-primary">Food Menu</h5>
            <h1>Explore Our Delicious Food</h1>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <input
                    type="search"
                    class="form-control"
                    placeholder="Search menu..."
                    wire:model.live.debounce.300ms="search"
                >
            </div>
            <div class="col-md-6">
                <select class="form-select" wire:model.live="categoryId">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
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
