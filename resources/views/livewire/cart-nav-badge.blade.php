<a href="{{ route('cart') }}" class="btn btn-outline-light btn-sm position-relative me-2">
    <i class="fa fa-shopping-cart"></i>
    @if ($count > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ $count }}
        </span>
    @endif
</a>
