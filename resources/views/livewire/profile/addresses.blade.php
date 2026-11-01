<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Delivery addresses</h2>
        <p class="mt-1 text-sm text-gray-600">Saved addresses are copied onto each order at checkout.</p>
    </header>

    <div class="mt-4 space-y-3">
        @forelse ($addresses as $address)
            <div class="flex items-start justify-between gap-3 border rounded-md p-3" wire:key="address-{{ $address->id }}">
                <div>
                    <p class="font-medium text-gray-900">{{ $address->label }} @if($address->is_default)<span class="text-xs text-green-700">Default</span>@endif</p>
                    <p class="text-sm text-gray-600">{{ $address->line }}, {{ $address->city }} {{ $address->postal_code }}</p>
                </div>
                <div class="flex gap-2">
                    @unless ($address->is_default)
                        <button type="button" class="text-sm text-gray-600 underline" wire:click="makeDefault({{ $address->id }})">Default</button>
                    @endunless
                    <button type="button" class="text-sm text-red-600 underline" wire:click="delete({{ $address->id }})">Remove</button>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-600">No addresses yet.</p>
        @endforelse
    </div>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div>
            <x-input-label for="label" value="Label" />
            <x-text-input wire:model="label" id="label" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('label')" />
        </div>
        <div>
            <x-input-label for="line" value="Street" />
            <x-text-input wire:model="line" id="line" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('line')" />
        </div>
        <div>
            <x-input-label for="city" value="City" />
            <x-text-input wire:model="city" id="city" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>
        <div>
            <x-input-label for="postal_code" value="Postal code" />
            <x-text-input wire:model="postal_code" id="postal_code" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_default" wire:model="is_default" class="rounded border-gray-300">
            <label for="is_default" class="text-sm text-gray-700">Use as default</label>
        </div>
        <x-primary-button>Save address</x-primary-button>
    </form>
</div>
