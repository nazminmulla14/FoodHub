<?php

use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use App\Livewire\AboutPage;
use App\Livewire\Admin\BookingsIndex;
use App\Livewire\Admin\CategoriesIndex;
use App\Livewire\Admin\ChatsIndex;
use App\Livewire\Admin\ContactMessagesIndex;
use App\Livewire\Admin\CouponsIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\MenuItemForm;
use App\Livewire\Admin\MenuItemsIndex;
use App\Livewire\Admin\NewsletterSubscribersIndex;
use App\Livewire\Admin\OrdersIndex;
use App\Livewire\Admin\RestaurantHours;
use App\Livewire\Admin\RestaurantsIndex;
use App\Livewire\Admin\ReviewsIndex;
use App\Livewire\BookingPage;
use App\Livewire\CartPage;
use App\Livewire\CheckoutPage;
use App\Livewire\ContactPage;
use App\Livewire\HomePage;
use App\Livewire\LegalPage;
use App\Livewire\MyBookingsPage;
use App\Livewire\OrderChatPage;
use App\Livewire\OrderHistoryPage;
use App\Livewire\Owner\BookingsIndex as OwnerBookingsIndex;
use App\Livewire\Owner\CategoriesIndex as OwnerCategoriesIndex;
use App\Livewire\Owner\ChatsIndex as OwnerChatsIndex;
use App\Livewire\Owner\Dashboard as OwnerDashboard;
use App\Livewire\Owner\MenuItemForm as OwnerMenuItemForm;
use App\Livewire\Owner\MenuItemsIndex as OwnerMenuItemsIndex;
use App\Livewire\Owner\OrdersIndex as OwnerOrdersIndex;
use App\Livewire\Owner\RestaurantHours as OwnerRestaurantHours;
use App\Livewire\Owner\ReviewsIndex as OwnerReviewsIndex;
use App\Livewire\RegisterRestaurantOwnerPage;
use App\Livewire\RestaurantMenuPage;
use App\Livewire\RestaurantsListPage;
use App\Livewire\TeamPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
Route::get('/about', AboutPage::class)->name('about');
Route::get('/menu', fn () => redirect()->route('restaurants.index'))->name('menu');
Route::get('/restaurants', RestaurantsListPage::class)->name('restaurants.index');
Route::get('/restaurants/{slug}/menu', RestaurantMenuPage::class)->name('restaurants.menu');
Route::get('/register-restaurant', RegisterRestaurantOwnerPage::class)->name('register-restaurant');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/booking', BookingPage::class)->name('booking');
Route::get('/team', TeamPage::class)->name('team');
Route::get('/contact', ContactPage::class)->name('contact');
Route::get('/privacy', LegalPage::class)->name('privacy');
Route::get('/terms', LegalPage::class)->name('terms');

Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('/orders', OrderHistoryPage::class)->name('orders.index');
    Route::get('/orders/{order}/chat', OrderChatPage::class)->name('orders.chat');
    Route::get('/my-bookings', MyBookingsPage::class)->name('bookings.index');

    Route::post('/logout', function () {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});


Route::redirect('/admin/menu_items', '/admin/menu-items');


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/orders', OrdersIndex::class)->name('orders');
    Route::get('/chats', ChatsIndex::class)->name('chats');
    Route::get('/bookings', BookingsIndex::class)->name('bookings');
    Route::get('/categories', CategoriesIndex::class)->name('categories');
    Route::get('/menu-items', MenuItemsIndex::class)->name('menu-items');
    Route::get('/menu-items/create', MenuItemForm::class)->name('menu-items.create');
    Route::get('/menu-items/{menuItem}/edit', MenuItemForm::class)->name('menu-items.edit');
    Route::get('/restaurants', RestaurantsIndex::class)->name('restaurants');
    Route::get('/contact-messages', ContactMessagesIndex::class)->name('contact-messages');
    Route::get('/newsletter', NewsletterSubscribersIndex::class)->name('newsletter');
    Route::get('/coupons', CouponsIndex::class)->name('coupons');
    Route::get('/reviews', ReviewsIndex::class)->name('reviews');
    Route::get('/hours', RestaurantHours::class)->name('hours');
    Route::get('/orders/export', [ExportController::class, 'orders'])->name('orders.export');
    Route::get('/bookings/export', [ExportController::class, 'bookings'])->name('bookings.export');
    Route::get('/newsletter/export', [ExportController::class, 'newsletter'])->name('newsletter.export');
});

Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', OwnerDashboard::class)->name('dashboard');
    Route::get('/orders', OwnerOrdersIndex::class)->name('orders');
    Route::get('/chats', OwnerChatsIndex::class)->name('chats');
    Route::get('/bookings', OwnerBookingsIndex::class)->name('bookings');
    Route::get('/categories', OwnerCategoriesIndex::class)->name('categories');
    Route::get('/menu-items', OwnerMenuItemsIndex::class)->name('menu-items');
    Route::get('/menu-items/create', OwnerMenuItemForm::class)->name('menu-items.create');
    Route::get('/menu-items/{menuItem}/edit', OwnerMenuItemForm::class)->name('menu-items.edit');
    Route::get('/reviews', OwnerReviewsIndex::class)->name('reviews');
    Route::get('/hours', OwnerRestaurantHours::class)->name('hours');
});

require __DIR__.'/auth.php';
