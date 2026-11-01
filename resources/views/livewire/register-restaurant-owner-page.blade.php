<div class="container-xxl py-5">
    <div class="container" style="max-width: 520px;">
        <h1 class="mb-4">Register your restaurant</h1>

        @if ($errorMessage)
            <div class="alert alert-danger">{{ $errorMessage }}</div>
        @endif

        <form wire:submit="register" class="vstack gap-3">
            <div>
                <label class="form-label">Your name</label>
                <input type="text" class="form-control" wire:model="name" required>
                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" class="form-control" wire:model="email" required>
                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" class="form-control" wire:model="password" required>
                @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">Confirm password</label>
                <input type="password" class="form-control" wire:model="password_confirmation" required>
            </div>
            <hr>
            <div>
                <label class="form-label">Restaurant name</label>
                <input type="text" class="form-control" wire:model="restaurant_name" required>
                @error('restaurant_name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">Address (optional)</label>
                <input type="text" class="form-control" wire:model="restaurant_address">
            </div>
            <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">Create account</button>
        </form>

        <p class="mt-3 small text-muted">
            Already have an account? <a href="{{ route('login') }}">Log in</a>
        </p>
    </div>
</div>
