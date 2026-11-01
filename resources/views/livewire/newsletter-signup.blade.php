<div>
    @if ($successMessage)
        <p class="text-success small mb-2">{{ $successMessage }}</p>
    @endif
    <form wire:submit="subscribe" class="position-relative mx-auto" style="max-width: 400px;">
        <input class="form-control border-primary w-100 py-3 ps-4 pe-5" type="email" placeholder="Your email" wire:model="email" required>
        <button type="submit" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2" wire:loading.attr="disabled">
            Sign Up
        </button>
        @error('email') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
    </form>
</div>
