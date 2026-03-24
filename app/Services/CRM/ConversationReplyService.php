<?php

namespace App\Services\CRM;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Line\LineApiService;

class ConversationReplyService
{
    public function __construct(
        protected LineApiService $lineApiService
    ) {}

    public function replyAsAgent(Conversation $conversation, User $user, string $text): Message
    {
        $customer = $conversation->customer;

        $this->lineApiService->pushText($customer->line_user_id, $text);

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