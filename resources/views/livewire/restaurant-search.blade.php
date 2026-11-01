<div class="position-relative" style="min-width: 220px;">
    <div class="input-group input-group-sm">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            class="form-control rounded-pill px-3 py-1 bg-light text-dark border-0" 
            placeholder="Search restaurants..."
        >
        @if (!empty($search))
            <button 
                type="button" 
                wire:click="$set('search', '')" 
                class="btn btn-sm text-secondary position-absolute end-0 top-50 translate-middle-y me-1"
                style="z-index: 5; background: none; border: none;"
            >
                <i class="fa fa-times"></i>
            </button>
        @endif
    </div>

    @if (strlen(trim($search)) >= 2)
        <div class="position-absolute bg-white text-dark shadow rounded mt-1 w-100 overflow-hidden" style="z-index: 1050; max-height: 280px; overflow-y: auto;">
            @forelse ($restaurants as $restaurant)
                <a href="{{ route('restaurants.menu', $restaurant->slug) }}" class="d-flex align-items-center p-2 text-decoration-none text-dark border-bottom">
                    @if ($restaurant->image)
                        <img src="{{ asset($restaurant->image) }}" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;" alt="{{ $restaurant->name }}">
                    @else
                        <div class="rounded bg-primary text-white d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">
                            <i class="fa fa-utensils"></i>
                        </div>
                    @endif
                    <div class="overflow-hidden">
                        <div class="fw-bold small text-truncate">{{ $restaurant->name }}</div>
                        @if ($restaurant->address)
                            <small class="text-muted d-block text-truncate" style="font-size: 11px;">{{ $restaurant->address }}</small>
                        @endif
                    </div>
                </a>
            @empty
                @if ($dishes->isEmpty())
                    <div class="p-2 text-center text-muted small">
                        No restaurants found
                    </div>
                @endif
            @endforelse
            @foreach ($dishes as $dish)
                <a href="{{ route('restaurants.menu', $dish->restaurant->slug) }}" class="d-flex align-items-center p-2 text-decoration-none text-dark border-bottom">
                    <div class="overflow-hidden">
                        <div class="fw-bold small text-truncate">{{ $dish->name }}</div>
                        <small class="text-muted d-block text-truncate">{{ $dish->restaurant->name }}</small>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>