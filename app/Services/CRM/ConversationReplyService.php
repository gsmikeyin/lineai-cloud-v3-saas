<?php

namespace App\Services\CRM;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Line\LineMessagingService;
use Illuminate\Support\Facades\Log;

class ConversationReplyService
{
    public function __construct(
        protected LineMessagingService $lineMessagingService
    ) {}

    public function replyAsAgent(Conversation $conversation, User $user, string $text): Message
    {
        $customer = $conversation->customer;
        $channel = $conversation->tenant->lineChannel;

        Log::info('Agent reply start', [
            'conversation_id' => $conversation->id,
            'customer_id' => $customer?->id,
            'line_user_id' => $customer?->line_user_id,
            'tenant_id' => $conversation->tenant_id,
            'has_channel' => !is_null($channel),
            'has_access_token' => !empty($channel?->channel_access_token),
        ]);

        if (!$customer || !$customer->line_user_id) {
            throw new \RuntimeException('Customer line_user_id not found.');
        }

        if (!$channel || empty($channel->channel_access_token)) {
            throw new \RuntimeException('LINE channel access token not configured.');
        }

        $this->lineMessagingService->push(
            $customer->line_user_id,
            $text,
            $channel->channel_access_token
        );

        $message = Message::create([
            'tenant_id' => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'direction' => Message::DIRECTION_OUTBOUND,
            'sender_type' => Message::SENDER_AGENT,
            'message_type' => Message::TYPE_TEXT,
            'content' => $text,
            'is_ai_generated' => false,
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        $conversation->update([
            'assigned_user_id' => $user->id,
            'human_handoff' => true,
            'ai_enabled' => false,
            'status' => Conversation::STATUS_PENDING,
            'last_message_at' => now(),
            'last_agent_reply_at' => now(),
            'unread_count' => 0,
            'last_read_at' => now(),
        ]);

        return $message;
    }
}