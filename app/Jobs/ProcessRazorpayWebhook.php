<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use App\Models\RazorpayWebhookEvent;
use App\Services\Orders\ConfirmOrderPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRazorpayWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(public int $eventRowId) {}

    public function handle(ConfirmOrderPaymentService $confirm): void
    {
        $row = RazorpayWebhookEvent::find($this->eventRowId);

        if (! $row || $row->processed_at) {
            return;
        }

        $payload = $row->payload;
        $paymentEntity = $payload['payload']['payment']['entity'] ?? [];

        try {
            if (in_array($row->event_type, ['payment.captured', 'order.paid'], true)) {
                $razorpayOrderId = (string) ($paymentEntity['order_id'] ?? '');
                $razorpayPaymentId = (string) ($paymentEntity['id'] ?? '');

                if ($razorpayOrderId === '' || $razorpayPaymentId === '') {
                    $row->processed_at = now();
                    $row->save();

                    return;
                }

                $payment = Payment::query()->where('razorpay_order_id', $razorpayOrderId)->first();
                if (! $payment) {
                    throw new \RuntimeException('payment_not_found');
                }

                $order = Order::query()->find($payment->order_id);
                if (! $order) {
                    throw new \RuntimeException('order_not_found');
                }

                $amountPaise = isset($paymentEntity['amount']) ? (int) $paymentEntity['amount'] : null;

                $confirm->confirmFromGatewayCapture(
                    $order,
                    $razorpayOrderId,
                    $razorpayPaymentId,
                    $amountPaise,
                );
            }

            $row->processed_at = now();
            $row->save();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
