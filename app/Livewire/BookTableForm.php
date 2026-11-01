<?php

namespace App\Livewire;

use App\Jobs\SendBookingConfirmation;
use App\Models\Restaurant;
use App\Services\Booking\CreateTableBookingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class BookTableForm extends Component
{
    public string $variant = 'page';

    public ?int $restaurant_id = null;

    public string $guest_name = '';

    public string $guest_email = '';

    public string $guest_phone = '';

    public int $party_size = 2;

    public string $booked_at = '';

    public string $special_request = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    /** @var Collection<int, Restaurant>|null */
    private ?Collection $restaurantList = null;

    public function mount(): void
    {
        $restaurants = $this->restaurants();

        $slug = (string) request('restaurant', '');
        if ($slug !== '') {
            $match = $restaurants->firstWhere('slug', $slug);
            if ($match) {
                $this->restaurant_id = $match->id;
            }
        }

        if ($this->restaurant_id === null) {
            $this->restaurant_id = $restaurants->first()?->id;
        }

        if (auth()->check()) {
            $this->guest_name = auth()->user()->name;
            $this->guest_email = auth()->user()->email;
        }
    }

    public function book(CreateTableBookingService $bookingService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $publicIds = $this->restaurants()->modelKeys();

        $validated = $this->validate([
            'restaurant_id' => ['required', 'integer', function (string $attribute, mixed $value, \Closure $fail) use ($publicIds): void {
                if (! in_array((int) $value, $publicIds, true)) {
                    $fail('Choose a restaurant that is accepting bookings.');
                }
            }],
            'guest_name' => ['required', 'string', 'max:120'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:30'],
            'party_size' => ['required', 'integer', 'min:1', 'max:20'],
            'booked_at' => ['required', 'date', 'after:now'],
            'special_request' => ['nullable', 'string', 'max:1000'],
        ]);

        $restaurant = $this->restaurants()->firstWhere('id', (int) $validated['restaurant_id']);

        try {
            $booking = $bookingService->create(
                restaurant: $restaurant,
                guestName: $validated['guest_name'],
                guestEmail: $validated['guest_email'],
                guestPhone: $validated['guest_phone'] ?: null,
                partySize: (int) $validated['party_size'],
                bookedAt: Carbon::parse($validated['booked_at']),
                specialRequest: $validated['special_request'] ?: null,
                user: auth()->user(),
            );
        } catch (\RuntimeException $e) {
            $this->errorMessage = $e->getMessage();

            return;
        }

        SendBookingConfirmation::dispatch($booking);

        $this->reset(['guest_name', 'guest_email', 'guest_phone', 'party_size', 'booked_at', 'special_request']);
        $this->party_size = 2;
        if (auth()->check()) {
            $this->guest_name = auth()->user()->name;
            $this->guest_email = auth()->user()->email;
        }
        $this->successMessage = 'Your table request was submitted. We will confirm shortly.';
    }

    public function render()
    {
        return view('livewire.book-table-form', [
            'restaurants' => $this->restaurants(),
        ]);
    }

    /**
     * @return Collection<int, Restaurant>
     */
    private function restaurants(): Collection
    {
        return $this->restaurantList ??= Restaurant::query()
            ->publiclyVisible()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }
}
