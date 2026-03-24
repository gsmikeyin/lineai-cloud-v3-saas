<?php

namespace App\Services\CRM;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;

class ConversationMessageService
{
    public function findOrCreateCustomer(Tenant $tenant, string $lineUserId, ?array $profile = null): Customer
    {
        return Customer::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'line_user_id' => $lineUserId,
            ],
            [
                'source' => 'line',
                'display_name' => $profile['displayName'] ?? null,
                'avatar_url' => $profile['pictureUrl'] ?? null,
                'language' => $profile['language'] ?? null,
                'status' => 'active',
                'first_interaction_at' => now(),
                'last_interaction_at' => now(),
            ]
        );
    }

    public function touchCustomer(Customer $customer, ?array $profile = null): Customer
    {
        $customer->update([
            'display_name' => $profile['displayName'] ?? $customer->display_name,
            'avatar_url' => $profile['pictureUrl'] ?? $customer->avatar_url,
            'language' => $profile['language'] ?? $customer->language,
            'last_interaction_at' => now(),
            'total_messages' => (int) $customer->total_messages + 1,
        ]);

        return $customer->refresh();
    }

    public function findOrCreateConversation(Tenant $tenant, Customer $customer): Conversation
    {
        return Conversation::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'status' => 'open',
            ],
            [
                'channel' => 'line',
                'priority' => 'normal',
                'ai_enabled' => true,
                'human_handoff' => false,
                'last_message_at' => now(),
            ]
        );
    }

    public function createInboundMessage(Tenant $tenant, Conversation $conversation, Customer $customer, array $event): Message
    {
        $text = data_get($event, 'message.text');

        $message = Message::create([
            'tenant_id' => $tenant->id,
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'direction' => 'inbound',
            'sender_type' => 'customer',
            'message_type' => data_get($event, 'message.type', 'text'),
            'content' => $text,
            'line_message_id' => data_get($event, 'message.id'),
            'reply_token' => data_get($event, 'replyToken'),
            'raw_payload' => $event,
            'sent_at' => now(),
        ]);

         $conversation->update([
             'last_message_at' => now(),
             'last_customer_message_at' => now(),
        ]); 

        $conversation->increment('unread_count');

        return $message;
    }

    public function createOutboundAiMessage(Tenant $tenant, Conversation $conversation, Customer $customer, string $reply, array $meta = []): Message
    {
        $message = Message::create([
            'tenant_id' => $tenant->id,
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'direction' => 'outbound',
            'sender_type' => 'ai',
            'message_type' => 'text',
            'content' => $reply,
            'is_ai_generated' => true,
            'meta' => $meta,
            'sent_at' => now(),
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_agent_reply_at' => now(),
        ]);

        return $message;
    }
}