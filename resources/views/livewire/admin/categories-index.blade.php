<div>
    <h1 class="h3 mb-4 fw-semibold">Categories</h1>

    @error('name')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <form wire:submit="save" class="admin-card mb-4" style="max-width: 520px;">
        <label class="form-label">New category name</label>
        <div class="input-group">
            <input type="text" class="form-control" wire:model="name" placeholder="e.g. Desserts">
            <button type="submit" class="btn btn-primary">Add</button>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Menu items</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr wire:key="cat-{{ $category->id }}">
                        <td>
                            @if ($editingId === $category->id)
                                <input type="text" class="form-control form-control-sm" wire:model="editName">
                            @else
                                {{ $category->name }}
                            @endif
                        </td>
                        <td><code class="small">{{ $category->slug }}</code></td>
                        <td>{{ $category->menu_items_count }}</td>
                        <td class="text-end">
                            @if ($editingId === $category->id)
                                <button type="button" class="btn btn-sm btn-primary" wire:click="updateCategory">Save</button>
                                <button type="button" class="btn btn-sm btn-secondary" wire:click="cancelEdit">Cancel</button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="startEdit({{ $category->id }})">Edit</button>
                                @if ($category->menu_items_count === 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="delete({{ $category->id }})" wire:confirm="Delete this category?">Delete</button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $categories->links() }}</div>
</div>
