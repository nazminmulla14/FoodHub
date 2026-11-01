<div>
    <h1 class="h3 mb-4 fw-semibold">Opening hours</h1>

    @if (session('admin_success'))
        <div class="alert alert-success">{{ session('admin_success') }}</div>
    @endif

    <form wire:submit="save" class="admin-card" style="max-width: 720px;">
        @foreach ($days as $index => $day)
            <div class="row g-2 align-items-center mb-2" wire:key="day-{{ $day['weekday'] }}">
                <div class="col-md-3 fw-medium">{{ $day['name'] }}</div>
                <div class="col-md-3">
                    <input type="time" class="form-control" wire:model="days.{{ $index }}.opens_at">
                </div>
                <div class="col-md-3">
                    <input type="time" class="form-control" wire:model="days.{{ $index }}.closes_at">
                </div>
                <div class="col-md-3 form-check">
                    <input type="checkbox" class="form-check-input" id="closed-{{ $index }}" wire:model="days.{{ $index }}.is_closed">
                    <label class="form-check-label" for="closed-{{ $index }}">Closed</label>
                </div>
            </div>
        @endforeach
        @error('days.*.opens_at') <div class="text-danger small">Enter opening times as HH:MM.</div> @enderror
        <button class="btn btn-primary mt-3" type="submit">Save hours</button>
    </form>
</div>
