<?php

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderChat
{
    public function send(Order $order, User $sender, string $body): Message
    {
        return DB::transaction(function () use ($order, $sender, $body) {
            $conversation = Conversation::query()->firstOrCreate(
                ['order_id' => $order->id],
                [
                    'restaurant_id' => $order->restaurant_id,
                    'customer_id' => $order->user_id,
                ],
            );

            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'body' => trim($body),
            ]);

            $conversation->forceFill([
                'last_message_at' => $message->created_at,
            ])->save();

            return $message;
        });
    }

    public function markRead(Conversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
