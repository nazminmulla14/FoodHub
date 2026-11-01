<div>
    @if ($showSelector)
        <div class="d-flex align-items-center gap-2">
            <label for="panel-restaurant" class="text-muted small mb-0">Restaurant</label>
            <select id="panel-restaurant" class="form-select form-select-sm" style="min-width: 12rem" wire:model.live="restaurantId">
                @foreach ($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                @endforeach
            </select>
        </div>
    @elseif ($restaurants->isNotEmpty())
        <span class="text-muted small">{{ $restaurants->first()->name }}</span>
    @endif
</div>
