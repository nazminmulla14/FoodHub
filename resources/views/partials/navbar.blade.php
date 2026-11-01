<div class="container-fluid p-0">
    <div class="container position-relative p-0">
        <nav @class([
            'navbar navbar-expand-lg navbar-dark bg-dark p-3 py-lg-0',
            'sticky-top shadow-sm' => ! request()->routeIs('home'),
        ])>
            <a href="{{ route('home') }}" class="navbar-brand p-0">
                <h1 class="text-primary m-0"><i class="fa fa-utensils me-3"></i>FoodHub</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0 pe-4">
                    <a href="{{ route('home') }}" class="nav-item nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('about') }}" class="nav-item nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                    <a href="{{ route('restaurants.index') }}" class="nav-item nav-link {{ request()->routeIs('restaurants.*') ? 'active' : '' }}">Restaurants</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Pages</a>
                        <div class="dropdown-menu m-0">
                            <a href="{{ route('booking') }}" class="dropdown-item">Booking</a>
                            <a href="{{ route('team') }}" class="dropdown-item">Our Team</a>
                        </div>
                    </div>
                    <a href="{{ route('contact') }}" class="nav-item nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                    @auth
                        <a href="{{ route('orders.index') }}" class="nav-item nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">Orders</a>
                        <a href="{{ route('bookings.index') }}" class="nav-item nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}">Bookings</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="nav-item nav-link">Admin</a>
                        @endif
                        @if (auth()->user()->isRestaurantOwner())
                            <a href="{{ route('owner.dashboard') }}" class="nav-item nav-link">Owner</a>
                        @endif
                    @endauth
                </div>
                <div class="d-flex align-items-center me-3 my-2 my-lg-0">
                    <livewire:restaurant-search />
                    <livewire:cart-nav-badge />
                </div>
                @guest
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-outline-light py-2 px-3 me-2">Login</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-outline-primary py-2 px-3 me-2">Register</a>
                    @endif
                @else
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light py-2 px-3 me-2">Logout</button>
                        </form>
                    @endif
                @endguest
                <a href="{{ route('booking') }}" class="btn btn-primary py-2 px-4">Book A Table</a>
            </div>
        </nav>
    </div>

    @if (request()->routeIs('home'))
        <div class="container-fluid py-5 bg-dark hero-header mb-5">
            <div class="container my-5 py-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 text-center text-lg-start">
                        <h1 class="display-3 text-white animated slideInLeft">Enjoy Our<br>Delicious Meal</h1>
                        <p class="text-white animated slideInLeft mb-4 pb-2">Order online or book a table at FoodHub Kitchen.</p>
                        <a href="{{ route('restaurants.index') }}" class="btn btn-primary py-sm-3 px-sm-5 me-3 animated slideInLeft">View Restaurants</a>
                        <a href="{{ route('booking') }}" class="btn btn-outline-light py-sm-3 px-sm-5 animated slideInLeft">Book A Table</a>
                    </div>
                    <div class="col-lg-6 text-center text-lg-end overflow-hidden">
                        <img class="img-fluid" src="{{ asset('img/hero.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
