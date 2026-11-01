<div class="container-xxl py-5">
    <div class="container">
        <h1 class="mb-4">Your Cart</h1>

        @if (session('cart_notice'))
            <div class="alert alert-info">{{ session('cart_notice') }}</div>
        @endif

        @if ($lines->isEmpty())
            <p>Your cart is empty. <a href="{{ route('restaurants.index') }}">Browse restaurants</a>.</p>
        @else
            @if ($restaurantName)
                <p class="text-muted">Ordering from <strong>{{ $restaurantName }}</strong></p>
            @endif
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Line total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            <tr wire:key="line-{{ $line['menu_item']->id }}-{{ $line['options_key'] }}">
                                <td>
                                    {{ $line['menu_item']->name }}
                                    @if ($line['options_label'])
                                        <div class="small text-muted">{{ $line['options_label'] }}</div>
                                    @endif
                                </td>
                                <td>₹{{ number_format($line['unit_price'], 2) }}</td>
                                <td>
                                    <input
                                        type="number"
                                        min="1"
                                        class="form-control form-control-sm"
                                        style="width: 5rem"
                                        value="{{ $line['quantity'] }}"
                                        wire:change="updateQuantity({{ $line['menu_item']->id }}, $event.target.value, '{{ $line['options_key'] }}')"
                                    >
                                </td>
                                <td>₹{{ number_format($line['line_total'], 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="remove({{ $line['menu_item']->id }}, '{{ $line['options_key'] }}')">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <h5>Subtotal: ₹{{ number_format($subtotal, 2) }}</h5>
                <a href="{{ route('checkout') }}" class="btn btn-primary">Proceed to checkout</a>
            </div>
        @endif
    </div>
</div>
