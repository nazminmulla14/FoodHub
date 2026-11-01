<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'FoodHub Owner' }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @livewireStyles
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <a href="{{ route('owner.dashboard') }}" class="admin-sidebar-brand">
                <i class="fa fa-store me-2"></i>Food<span>Hub</span> Owner
            </a>

            <nav>
                <ul class="admin-sidebar-nav">
                    <li>
                        <a href="{{ route('owner.dashboard') }}" class="{{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                            <i class="fa fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.orders') }}" class="{{ request()->routeIs('owner.orders') ? 'active' : '' }}">
                            <i class="fa fa-shopping-bag"></i> Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.chats') }}" class="{{ request()->routeIs('owner.chats') ? 'active' : '' }}">
                            <i class="fa fa-comments"></i> Chats
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.bookings') }}" class="{{ request()->routeIs('owner.bookings') ? 'active' : '' }}">
                            <i class="fa fa-calendar-alt"></i> Bookings
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.categories') }}" class="{{ request()->routeIs('owner.categories') ? 'active' : '' }}">
                            <i class="fa fa-tags"></i> Categories
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.menu-items') }}" class="{{ request()->routeIs('owner.menu-items*') ? 'active' : '' }}">
                            <i class="fa fa-list"></i> Menu items
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.reviews') }}" class="{{ request()->routeIs('owner.reviews') ? 'active' : '' }}">
                            <i class="fa fa-star"></i> Reviews
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.hours') }}" class="{{ request()->routeIs('owner.hours') ? 'active' : '' }}">
                            <i class="fa fa-clock"></i> Hours
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ route('restaurants.index') }}" target="_blank" rel="noopener">
                    <i class="fa fa-external-link-alt me-1"></i> Public restaurants
                </a>
                @auth
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-decoration-none" style="color: var(--admin-sidebar-muted); font-size: 0.875rem;">
                                <i class="fa fa-sign-out-alt me-1"></i> Logout
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <p class="admin-topbar-title mb-0">{{ $header ?? 'Your restaurant' }}</p>
                <div class="admin-topbar-actions d-flex align-items-center gap-3">
                    @auth
                        <livewire:admin.restaurant-selector />
                        <span class="text-muted small">{{ auth()->user()->name }}</span>
                    @endauth
                </div>
            </header>

            <main class="admin-content">
                @if (session('owner_registered'))
                    <div class="alert alert-info">Your restaurant is pending platform approval. You can still set up your menu; it will appear publicly once approved.</div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
