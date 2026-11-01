
<div class="col-lg-6">
    <div class="d-flex align-items-center">

        @if ($item->image_url)
            <img
                src="{{ $item->image_url }}"
                alt="{{ $item->name }}"
                class="flex-shrink-0 img-fluid rounded"
                style="width: 100px; height: 100px; object-fit: cover;"
            >
        @endif

        <div class="w-100 d-flex flex-column text-start ps-4">

            <h5 class="d-flex justify-content-between border-bottom pb-2">
                <span>
                    {{ $item->name }}
                    @if ($item->is_veg)
                        <span class="badge text-bg-success">Veg</span>
                    @endif
                    @if (! $item->is_available)
                        <span class="badge text-bg-secondary">Sold out</span>
                    @endif
                </span>

                <span class="text-primary">
                    ₹{{ number_format((float) $item->price, 2) }}
                </span>
            </h5>

            <small class="fst-italic">
                {{ $item->description }}
            </small>

            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">
                    {{ $item->category->name }}
                    ·
                    {{ $item->restaurant->name }}
                </small>

                <livewire:add-to-cart
                    :menu-item-id="$item->id"
                    :disabled="! $item->is_available || ! $item->restaurant->isOpen()"
                    :key="'cart-'.$item->id"
                />
            </div>

        </div>
    </div>
</div>