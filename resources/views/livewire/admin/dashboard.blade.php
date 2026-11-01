<div>
    <h1 class="h3 mb-4 fw-semibold">Dashboard</h1>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="admin-stat-card">
                <div class="label">Total orders</div>
                <div class="value">{{ $ordersCount }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card">
                <div class="label">Pending bookings</div>
                <div class="value">{{ $pendingBookings }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="label">Customers</div>
                <div class="value">{{ $customersCount }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="label">Today's revenue</div>
                <div class="value">₹{{ number_format((float) $todayRevenue, 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="label">This week</div>
                <div class="value">₹{{ number_format((float) $weekRevenue, 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="label">Bookings</div>
                <div class="value">{{ $bookingCount }}</div>
            </div>
        </div>
    </div>

    <div class="admin-card mb-4">
        <h2 class="h6 text-muted mb-3">Top dishes</h2>
        @forelse ($topDishes as $dish)
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>{{ $dish->name }}</span>
                <span>{{ $dish->units }} sold</span>
            </div>
        @empty
            <p class="text-muted mb-0">No paid orders yet.</p>
        @endforelse
    </div>

    <div class="admin-card">
        <h2 class="h6 text-muted mb-3">Quick links</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ \App\Support\Panel::route('orders') }}" class="btn btn-outline-secondary btn-sm">Manage orders</a>
            <a href="{{ \App\Support\Panel::route('menu-items.create') }}" class="btn btn-primary btn-sm">Add menu item</a>
            <a href="{{ \App\Support\Panel::route('bookings') }}" class="btn btn-outline-secondary btn-sm">View bookings</a>
        </div>
    </div>
</div>
