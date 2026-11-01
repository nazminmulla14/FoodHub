<div class="container-xxl py-5">
    <div class="container">
        <h1 class="mb-4">Your Orders</h1>

        @if (session('order_success'))
            <div class="alert alert-success">Order #{{ session('order_success') }} placed successfully.</div>
        @endif

        @if ($notice)
            <div class="alert alert-info">{{ $notice }}</div>
        @endif

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Restaurant</th>
                        <th>Order status</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr wire:key="order-row-{{ $order->id }}">
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->restaurant->name }}</td>
                            <td>
                                <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $order->payment_status->badgeClass() }}">{{ $order->payment_status->label() }}</span>
                            </td>
                            <td>₹{{ number_format((float) $order->total, 2) }}</td>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if ($order->user_id === auth()->id())
                                    <a href="{{ route('orders.chat', $order) }}" class="btn btn-sm btn-outline-secondary me-1">
                                        Message
                                        @if (($order->conversation->unread_count ?? 0) > 0)
                                            <span class="badge bg-danger">{{ $order->conversation->unread_count }}</span>
                                        @endif
                                    </a>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="toggleOrder({{ $order->id }})">
                                    {{ $expandedOrderId === $order->id ? 'Hide' : 'Details' }}
                                </button>
                            </td>
                        </tr>
                        @if ($expandedOrderId === $order->id)
                            <tr wire:key="order-detail-{{ $order->id }}">
                                <td colspan="7" class="bg-light">
                                    @if ($order->status === \App\Enums\OrderStatus::Cancelled)
                                        <div class="alert alert-danger py-2 mb-3">
                                            This order was cancelled.
                                            @if ($order->payment_status === \App\Enums\PaymentStatus::Refunded)
                                                The payment has been refunded.
                                            @endif
                                        </div>
                                    @else
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                            @foreach (\App\Enums\OrderStatus::timeline() as $step)
                                                @php
                                                    $current = $order->status->timelineIndex();
                                                    $stepIndex = $step->timelineIndex();
                                                    $done = $current !== null && $stepIndex <= $current;
                                                    $currentStep = $current !== null && $stepIndex === $current;
                                                @endphp
                                                @if (! $loop->first)
                                                    <span class="text-muted small">›</span>
                                                @endif
                                                <span class="badge {{ $currentStep ? 'bg-primary' : ($done ? 'bg-success' : 'bg-light text-dark border') }}">{{ $step->label() }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <ul class="mb-2">
                                        @foreach ($order->items as $item)
                                            <li>
                                                {{ $item->quantity }} × {{ $item->name }}
                                                @if ($item->options_label)
                                                    ({{ $item->options_label }})
                                                @endif
                                                — ₹{{ number_format((float) $item->price * $item->quantity, 2) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if ($order->delivery_address)
                                        <p class="small mb-1">Deliver to: {{ $order->delivery_address }}</p>
                                    @endif
                                    @if ($order->notes)
                                        <p class="small text-muted mb-2">Note: {{ $order->notes }}</p>
                                    @endif
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="reorder({{ $order->id }})">Reorder</button>
                                        @if ($order->status->canBeCancelled())
                                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="askCancel({{ $order->id }})">Cancel order</button>
                                        @endif
                                    </div>
                                    @if ($order->review)
                                        <p class="mb-1">Your rating: {{ $order->review->rating }}/5</p>
                                        <p class="mb-1">{{ $order->review->body }}</p>
                                        @if ($order->review->owner_reply)
                                            <p class="small text-muted mb-0">Restaurant reply: {{ $order->review->owner_reply }}</p>
                                        @endif
                                    @elseif ($order->status === \App\Enums\OrderStatus::Completed && $order->user_id === auth()->id())
                                        <div class="border-top pt-3" style="max-width: 420px;">
                                            <label class="form-label">Rate this order</label>
                                            <select class="form-select form-select-sm mb-2" wire:model="reviewRating">
                                                @for ($stars = 5; $stars >= 1; $stars--)
                                                    <option value="{{ $stars }}">{{ $stars }}</option>
                                                @endfor
                                            </select>
                                            <textarea class="form-control mb-2" rows="2" wire:model="reviewBody" placeholder="How was the food?"></textarea>
                                            @error('reviewBody') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                                            <button type="button" class="btn btn-sm btn-primary" wire:click="submitReview({{ $order->id }})">Submit review</button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>

    @if ($confirmingOrder)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="cancel-order-title" wire:click.self="dismissCancel" style="background-color: rgba(15, 23, 43, 0.55);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancel-order-title">Cancel order #{{ $confirmingOrder->id }}</h5>
                        <button type="button" class="btn-close" wire:click="dismissCancel" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Cancel this order from {{ $confirmingOrder->restaurant->name }}? @if ($confirmingOrder->payment_status === \App\Enums\PaymentStatus::Paid) The paid amount of ₹{{ number_format((float) $confirmingOrder->total, 2) }} will be refunded. @endif</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="dismissCancel">Keep order</button>
                        <button type="button" class="btn btn-danger" wire:click="cancel({{ $confirmingOrder->id }})" wire:loading.attr="disabled" wire:target="cancel">
                            <span wire:loading.remove wire:target="cancel">Cancel order</span>
                            <span wire:loading wire:target="cancel">Cancelling...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
