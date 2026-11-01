<div class="container-xxl py-5">
    <div class="container" style="max-width: 640px;">
        <h1 class="mb-4">Checkout</h1>

        @if ($errorMessage)
            <div class="alert alert-danger">{{ $errorMessage }}</div>
        @endif

        <ul class="list-group mb-3">
            @foreach ($lines as $line)
                <li class="list-group-item d-flex justify-content-between">
                    <span>
                        {{ $line['menu_item']->name }} × {{ $line['quantity'] }}
                        @if ($line['options_label'])
                            <small class="text-muted d-block">{{ $line['options_label'] }}</small>
                        @endif
                    </span>
                    <span>₹{{ number_format($line['line_total'], 2) }}</span>
                </li>
            @endforeach
        </ul>

        @auth
            <div class="mb-3">
                <label class="form-label">Delivery address</label>
                @if ($addresses->isEmpty())
                    <p class="small mb-0">No saved address yet. <a href="{{ route('profile') }}">Add one on your profile</a>.</p>
                @else
                    <select class="form-select" wire:model="addressId">
                        @foreach ($addresses as $address)
                            <option value="{{ $address->id }}">{{ $address->snapshot() }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        @endauth

        <div class="mb-3">
            <label class="form-label">Promo code</label>
            <input type="text" class="form-control" wire:model.live.debounce.400ms="couponCode" placeholder="WELCOME10">
            @if ($couponApplied)
                <div class="form-text text-success">Promo applied.</div>
            @elseif ($couponCode !== '')
                <div class="form-text text-danger">This code cannot be used on this order.</div>
            @endif
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between"><span>Subtotal</span><span>₹{{ number_format($subtotal, 2) }}</span></div>
            @if ($discount > 0)
                <div class="d-flex justify-content-between"><span>Discount</span><span>-₹{{ number_format($discount, 2) }}</span></div>
            @endif
            <div class="d-flex justify-content-between"><span>Tax (5%)</span><span>₹{{ number_format($tax, 2) }}</span></div>
            <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>₹{{ number_format($total, 2) }}</span></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Order notes (optional)</label>
            <textarea class="form-control" rows="3" wire:model="notes" @if($checkout && !($checkout['mock'] ?? true)) disabled @endif></textarea>
        </div>

        @guest
            <p class="text-muted">You must be logged in to place an order.</p>
        @endguest

        @if ($checkout && ! ($checkout['mock'] ?? true))
            <button type="button" class="btn btn-primary w-100" wire:click="openRazorpayModal">
                Pay with Razorpay
            </button>
            <p class="small text-muted mt-2 mb-0">Complete payment in the Razorpay window.</p>
        @else
            <button type="button" class="btn btn-primary w-100" wire:click="startPayment" wire:loading.attr="disabled">
                @if ($razorpayEnabled)
                    Pay now
                @else
                    Place order (mock payment — no gateway keys)
                @endif
            </button>
        @endif
    </div>
</div>

@script
<script>
    const loadRazorpayCheckoutScript = () => new Promise((resolve, reject) => {
        if (window.Razorpay) {
            resolve();
            return;
        }

        const existing = document.querySelector('script[data-razorpay-checkout]');
        if (existing) {
            existing.addEventListener('load', () => resolve());
            existing.addEventListener('error', () => reject(new Error('Razorpay script failed to load')));
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.dataset.razorpayCheckout = '1';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Razorpay script failed to load'));
        document.head.appendChild(script);
    });

    const openRazorpayCheckout = async () => {
        const checkout = $wire.checkout;

        if (!checkout || checkout.mock) {
            return;
        }

        try {
            await loadRazorpayCheckoutScript();
        } catch (error) {
            console.error(error);
            alert('Could not load Razorpay. Check your connection or ad blocker.');

            return;
        }

        const rzp = new Razorpay({
            key: checkout.key,
            amount: checkout.amount_paise,
            currency: checkout.currency,
            order_id: checkout.order_id,
            name: 'FoodHub',
            description: 'Order #' + checkout.foodhub_order_id,
            handler: function (response) {
                $wire.confirmPayment(
                    response.razorpay_order_id,
                    response.razorpay_payment_id,
                    response.razorpay_signature
                );
            },
            theme: { color: '#FEA116' },
        });

        rzp.on('payment.failed', function (response) {
            console.error('Razorpay payment failed', response.error);
        });

        rzp.open();
    };

    $wire.on('razorpay-checkout-ready', () => {
        openRazorpayCheckout();
    });
</script>
@endscript
