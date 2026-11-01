<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0 fw-semibold">{{ $menuItem ? 'Edit menu item' : 'New menu item' }}</h1>
        <a href="{{ \App\Support\Panel::route('menu-items') }}" class="btn btn-outline-secondary btn-sm">Back to list</a>
    </div>

    <form wire:submit="save" class="admin-card" style="max-width: 640px;">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" wire:model="name">
            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3" wire:model="description"></textarea>
            @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" wire:model="category_id">
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Cuisine</label>
            <input type="text" class="form-control" wire:model="cuisine" placeholder="Italian, Burgers...">
            @error('cuisine') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_veg" wire:model="is_veg">
            <label class="form-check-label" for="is_veg">Vegetarian</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Price (₹)</label>
            <input type="number" step="0.01" min="0" class="form-control" wire:model="price">
            @error('price') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_available" wire:model="is_available">
            <label class="form-check-label" for="is_available">Available on menu</label>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label mb-0">Options</label>
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addOptionGroup">Add group</button>
            </div>
            @foreach ($optionGroups as $groupIndex => $group)
                <div class="border rounded p-2 mt-2" wire:key="group-{{ $groupIndex }}">
                    <input type="text" class="form-control form-control-sm mb-2" placeholder="Group name (Size, Spice)" wire:model="optionGroups.{{ $groupIndex }}.name">
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <input type="number" min="0" class="form-control form-control-sm" wire:model="optionGroups.{{ $groupIndex }}.min_select" placeholder="Min">
                        </div>
                        <div class="col-4">
                            <input type="number" min="1" class="form-control form-control-sm" wire:model="optionGroups.{{ $groupIndex }}.max_select" placeholder="Max">
                        </div>
                        <div class="col-4 form-check mt-2">
                            <input type="checkbox" class="form-check-input" wire:model="optionGroups.{{ $groupIndex }}.is_required" id="req-{{ $groupIndex }}">
                            <label class="form-check-label small" for="req-{{ $groupIndex }}">Required</label>
                        </div>
                    </div>
                    @foreach ($group['options'] as $optionIndex => $option)
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" class="form-control form-control-sm" placeholder="Option" wire:model="optionGroups.{{ $groupIndex }}.options.{{ $optionIndex }}.name">
                            <input type="number" step="0.01" class="form-control form-control-sm" style="max-width: 120px;" placeholder="Price +" wire:model="optionGroups.{{ $groupIndex }}.options.{{ $optionIndex }}.price_delta">
                        </div>
                    @endforeach
                    <button type="button" class="btn btn-sm btn-link" wire:click="addOption({{ $groupIndex }})">Add option</button>
                    <button type="button" class="btn btn-sm btn-link text-danger" wire:click="removeOptionGroup({{ $groupIndex }})">Remove group</button>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label class="form-label">Image</label>
            @if ($menuItem?->image_url)
                <div class="mb-2">
                    <img src="{{ $menuItem->image_url }}" alt="" class="rounded" style="max-height: 120px;">
                </div>
            @endif
            <input type="file" class="form-control" wire:model="imageUpload" accept="image/*">
            <div wire:loading wire:target="imageUpload" class="small text-muted">Uploading...</div>
            @error('imageUpload') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save</button>
    </form>
</div>
