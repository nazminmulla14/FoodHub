<div>
    @if ($successMessage)
        <div class="alert alert-success">{{ $successMessage }}</div>
    @endif
    @if ($errorMessage)
        <div class="alert alert-danger">{{ $errorMessage }}</div>
    @endif

    <form wire:submit="book">
        <div class="row g-3">
            @if ($restaurants->count() > 1)
                <div class="col-12">
                    @if ($variant === 'home')
                        <div class="form-floating">
                            <select class="form-select" id="book-restaurant" wire:model="restaurant_id">
                                @foreach ($restaurants as $restaurant)
                                    <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                                @endforeach
                            </select>
                            <label for="book-restaurant">Restaurant</label>
                        </div>
                    @else
                        <label class="form-label">Restaurant</label>
                        <select class="form-select" wire:model="restaurant_id">
                            @foreach ($restaurants as $restaurant)
                                <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('restaurant_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            @endif

            @if ($variant === 'home')
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="book-name" placeholder="Your Name" wire:model="guest_name">
                        <label for="book-name">Your Name</label>
                    </div>
                    @error('guest_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="book-email" placeholder="Your Email" wire:model="guest_email">
                        <label for="book-email">Your Email</label>
                    </div>
                    @error('guest_email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="datetime-local" class="form-control" id="book-datetime" wire:model="booked_at">
                        <label for="book-datetime">Date &amp; Time</label>
                    </div>
                    @error('booked_at') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select class="form-select" id="book-party" wire:model="party_size">
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}">People {{ $i }}</option>
                            @endfor
                        </select>
                        <label for="book-party">No Of People</label>
                    </div>
                    @error('party_size') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-12">
                    <div class="form-floating">
                        <textarea class="form-control" id="book-request" placeholder="Special Request" style="height: 100px" wire:model="special_request"></textarea>
                        <label for="book-request">Special Request</label>
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <label class="form-label">Your name</label>
                    <input type="text" class="form-control" wire:model="guest_name">
                    @error('guest_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" wire:model="guest_email">
                    @error('guest_email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" wire:model="guest_phone">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Party size</label>
                    <input type="number" min="1" max="20" class="form-control" wire:model="party_size">
                    @error('party_size') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Date &amp; time</label>
                    <input type="datetime-local" class="form-control" wire:model="booked_at">
                    @error('booked_at') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Special request</label>
                    <textarea class="form-control" rows="3" wire:model="special_request"></textarea>
                </div>
            @endif

            <div class="col-12">
                <button type="submit" class="btn btn-primary w-100 py-3" wire:loading.attr="disabled">
                    Book Now
                </button>
            </div>
        </div>
    </form>
</div>
