<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\ScopesOwnedRestaurants;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\TableBooking;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController
{
    use ScopesOwnedRestaurants;

    public function orders(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $orders = $this->scopeOwnedRestaurants(Order::query()->with(['user', 'restaurant']))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));

        return $this->download('orders.csv', ['ID', 'Customer', 'Restaurant', 'Status', 'Payment', 'Total', 'Created'], function ($handle) use ($orders) {
            $orders->reorder()->orderBy('id')->chunkById(200, function ($rows) use ($handle) {
                foreach ($rows as $order) {
                    fputcsv($handle, [
                        $order->id,
                        $order->user?->name,
                        $order->restaurant?->name,
                        $order->status->value,
                        $order->payment_status->value,
                        $order->total,
                        $order->created_at?->toDateTimeString(),
                    ]);
                }
            });
        });
    }

    public function bookings(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $bookings = $this->scopeOwnedRestaurants(TableBooking::query()->with('restaurant'));

        return $this->download('bookings.csv', ['ID', 'Guest', 'Email', 'Restaurant', 'When', 'Party', 'Status'], function ($handle) use ($bookings) {
            $bookings->reorder()->orderBy('id')->chunkById(200, function ($rows) use ($handle) {
                foreach ($rows as $booking) {
                    fputcsv($handle, [
                        $booking->id,
                        $booking->guest_name,
                        $booking->guest_email,
                        $booking->restaurant?->name,
                        $booking->booked_at?->toDateTimeString(),
                        $booking->party_size,
                        $booking->status->value,
                    ]);
                }
            });
        });
    }

    public function newsletter(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $subscribers = NewsletterSubscriber::query();

        return $this->download('newsletter-subscribers.csv', ['Email', 'Subscribed'], function ($handle) use ($subscribers) {
            $subscribers->orderBy('id')->chunkById(200, function ($rows) use ($handle) {
                foreach ($rows as $subscriber) {
                    fputcsv($handle, [
                        $subscriber->email,
                        $subscriber->created_at?->toDateTimeString(),
                    ]);
                }
            });
        });
    }

    private function download(string $filename, array $header, callable $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $writeRows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $header);
            $writeRows($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
