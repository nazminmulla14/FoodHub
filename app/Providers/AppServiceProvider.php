<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Listeners\QueueOrderConfirmation;
use App\Models\MenuItem;
use App\Observers\MenuItemObserver;
use App\Services\Cart\CartService;
use App\Services\Razorpay\RazorpayService;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RazorpayService::class, function () {
            $config = config('services.razorpay');

            return new RazorpayService(
                enabled: (bool) ($config['enabled'] ?? false),
                keyId: $config['key_id'] ?? null,
                keySecret: $config['key_secret'] ?? null,
                webhookSecret: $config['webhook_secret'] ?? null,
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        MenuItem::observe(MenuItemObserver::class);

        Event::listen(OrderPlaced::class, QueueOrderConfirmation::class);

        Event::listen(Login::class, function (Login $event): void {
            app(CartService::class)->mergeGuestCartIntoUser($event->user);
        });
    }
}
