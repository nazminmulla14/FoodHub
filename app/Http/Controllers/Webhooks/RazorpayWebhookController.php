<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessRazorpayWebhook;
use App\Models\RazorpayWebhookEvent;
use App\Services\Razorpay\RazorpayService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(private readonly RazorpayService $razorpay) {}

    public function __invoke(Request $request): JsonResponse
    {
        $raw = $request->getContent();

        if (! $this->razorpay->verifyWebhookSignature($raw, (string) $request->header('X-Razorpay-Signature', ''))) {
            Log::warning('[razorpay:webhook] signature failed');

            return response()->json(['success' => false], 401);
        }

        $payload = json_decode($raw, true);
        if (! is_array($payload) || empty($payload['event'])) {
            return response()->json(['success' => true, 'ignored' => true]);
        }

        $eventId = (string) ($request->header('X-Razorpay-Event-Id') ?: sha1($raw));

        try {
            $row = RazorpayWebhookEvent::create([
                'event_id' => $eventId,
                'event_type' => (string) $payload['event'],
                'payload' => $payload,
            ]);
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return response()->json(['success' => true, 'duplicate' => true]);
            }

            Log::error('[razorpay:webhook] store failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => true, 'stored' => false]);
        }

        ProcessRazorpayWebhook::dispatch($row->id);

        return response()->json(['success' => true]);
    }
}
