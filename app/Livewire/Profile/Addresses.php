<?php

namespace App\Livewire\Profile;

use App\Models\Address;
use Livewire\Component;

class Addresses extends Component
{
    public string $label = 'Home';

    public string $line = '';

    public string $city = '';

    public string $postal_code = '';

    public bool $is_default = false;

    public function save(): void
    {
        $validated = $this->validate([
            'label' => ['required', 'string', 'max:40'],
            'line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:80'],
            'postal_code' => ['required', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ]);

        $user = auth()->user();

        if ($validated['is_default'] || ! $user->addresses()->exists()) {
            $user->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $user->addresses()->create($validated);

        $this->reset(['line', 'city', 'postal_code']);
        $this->label = 'Home';
        $this->is_default = false;
    }

    public function delete(int $addressId): void
    {
        $address = Address::query()->where('user_id', auth()->id())->findOrFail($addressId);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            auth()->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }
    }

    public function makeDefault(int $addressId): void
    {
        $user = auth()->user();
        $address = Address::query()->where('user_id', $user->id)->findOrFail($addressId);
        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    public function render()
    {
        return view('livewire.profile.addresses', [
            'addresses' => auth()->user()->addresses()->orderByDesc('is_default')->get(),
        ]);
    }
}
