<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">Food Menu</h5>
            <h1 class="mb-5">Most Popular Items</h1>
        </div>
        <div class="row g-4">
            @forelse ($popularItems as $item)
                <div class="col-lg-6">
                    <div class="d-flex align-items-center">
                        @if ($item->image_url)
                            <img class="flex-shrink-0 img-fluid rounded" src="{{ $item->image_url }}" alt="{{ $item->name }}" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <img class="flex-shrink-0 img-fluid rounded" src="{{ asset('img/menu-1.jpg') }}" alt="" style="width: 80px;">
                        @endif
                        <div class="w-100 d-flex flex-column text-start ps-4">
                            <h5 class="d-flex justify-content-between border-bottom pb-2">
                                <span>{{ $item->name }}</span>
                                <span class="text-primary">₹{{ number_format((float) $item->price, 0) }}</span>
                            </h5>
                            <small class="fst-italic">{{ \Illuminate\Support\Str::limit($item->description ?? $item->restaurant->name, 60) }}</small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">
                    <p>No menu items yet.</p>
                    <a href="{{ route('restaurants.index') }}" class="btn btn-primary">Browse restaurants</a>
                </div>
            @endforelse
        </div>
        @if ($popularItems->isNotEmpty())
            <div class="text-center mt-4">
                <a href="{{ route('restaurants.index') }}" class="btn btn-primary py-3 px-5">View all restaurants</a>
            </div>
        @endif
    </div>
</div>
