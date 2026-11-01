<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'FoodHub Admin' }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @livewireStyles
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
                <i class="fa fa-utensils me-2"></i>Food<span>Hub</span> Admin
            </a>

            <nav>
                <ul class="admin-sidebar-nav">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fa fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders') }}" class="{{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                            <i class="fa fa-shopping-bag"></i> Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.chats') }}" class="{{ request()->routeIs('admin.chats') ? 'active' : '' }}">
                            <i class="fa fa-comments"></i> Chats
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.bookings') }}" class="{{ request()->routeIs('admin.bookings') ? 'active' : '' }}">
                            <i class="fa fa-calendar-alt"></i> Bookings
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.contact-messages') }}" class="{{ request()->routeIs('admin.contact-messages') ? 'active' : '' }}">
                            <i class="fa fa-envelope"></i> Contact inbox
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.newsletter') }}" class="{{ request()->routeIs('admin.newsletter') ? 'active' : '' }}">
                            <i class="fa fa-newspaper"></i> Newsletter
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.categories') }}" class="{{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                            <i class="fa fa-tags"></i> Categories
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.menu-items') }}" class="{{ request()->routeIs('admin.menu-items*') ? 'active' : '' }}">
                            <i class="fa fa-list"></i> Menu items
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.restaurants') }}" class="{{ request()->routeIs('admin.restaurants') ? 'active' : '' }}">
                            <i class="fa fa-store"></i> Restaurants
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.coupons') }}" class="{{ request()->routeIs('admin.coupons') ? 'active' : '' }}">
                            <i class="fa fa-ticket-alt"></i> Coupons
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reviews') }}" class="{{ request()->routeIs('admin.reviews') ? 'active' : '' }}">
                            <i class="fa fa-star"></i> Reviews
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.hours') }}" class="{{ request()->routeIs('admin.hours') ? 'active' : '' }}">
                            <i class="fa fa-clock"></i> Hours
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ route('home') }}" target="_blank" rel="noopener">
                    <i class="fa fa-external-link-alt me-1"></i> View public site
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
                <p class="admin-topbar-title mb-0">{{ $header ?? 'Administration' }}</p>
                <div class="admin-topbar-actions d-flex align-items-center gap-3">
                    @auth
                        <livewire:admin.restaurant-selector />
                        <span class="text-muted small">{{ auth()->user()->name }}</span>
                    @endauth
                </div>
            </header>

            <main class="admin-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
