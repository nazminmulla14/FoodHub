<div class="container-xxl py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <h1 class="mb-0">Restaurants</h1>
            <a href="{{ route('register-restaurant') }}" class="btn btn-outline-primary btn-sm">List your restaurant</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="search" class="form-control" placeholder="Search by name or city..." wire:model.live.debounce.300ms="search">
            </div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="cuisine">
                    <option value="">All cuisines</option>
                    @foreach ($cuisines as $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" wire:model.live="minRating">
                    <option value="">Any rating</option>
                    <option value="4">4+ stars</option>
                    <option value="3">3+ stars</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="vegOnly" wire:model.live="vegOnly">
                    <label class="form-check-label" for="vegOnly">Veg dishes available</label>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @forelse ($restaurants as $restaurant)
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        @if ($restaurant->image)
                            <img src="{{ asset($restaurant->image) }}" class="card-img-top" alt="{{ $restaurant->name }}" style="height: 180px; object-fit: cover;">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                {{ $restaurant->name }}
                                @if ($restaurant->reviews_count)
                                    <small class="text-muted">{{ number_format((float) $restaurant->reviews_avg_rating, 1) }} ({{ $restaurant->reviews_count }})</small>
                                @endif
                            </h5>
                            @if ($restaurant->address)
                                <p class="text-muted small mb-2">{{ $restaurant->address }}</p>
                            @endif
                            <p class="card-text small flex-grow-1">{{ \Illuminate\Support\Str::limit($restaurant->description ?? '', 120) }}</p>
                            <a href="{{ route('restaurants.menu', $restaurant->slug) }}" class="btn btn-primary mt-2">View menu</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No restaurants found.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $restaurants->links() }}
        </div>
    </div>
</div>
