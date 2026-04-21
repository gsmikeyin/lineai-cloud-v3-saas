<?php

namespace App\Services\Line;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Tenant;
use App\Services\AI\DifyChatService;
use Illuminate\Support\Facades\Log;

class LineWebhookEventProcessor
{
    public function __construct(
        protected DifyChatService $difyChatService,
        protected LineMessagingService $lineMessagingService,
    ) {}

    public function processTextMessage(Tenant $tenant, array $event, Customer $customer, Conversation $conversation, string $userMessage): void
    {
        $replyToken = data_get($event, 'replyToken');
        $lineUserId = $customer->line_user_id;
        $channelAccessToken = $tenant->lineChannel?->channel_access_token;

        Message::create([
            'tenant_id' => $tenant->id,
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'direction' => 'inbound',
            'sender_type' => 'customer',
            'message_type' => 'text',
            'content' => $userMessage,
            'is_ai_generated' => false,
            'sent_at' => now(),
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'unread_count' => ($conversation->unread_count ?? 0) + 1,
        ]);

        if ($conversation->human_handoff) {
            if ($replyToken) {
                $this->lineMessagingService->reply(
                    $replyToken,
                    '您好，您的訊息已轉交真人客服處理，請稍候。',
                    $channelAccessToken
                );
            }
            return;
        }

        try {
            $difyResult = $this->difyChatService->reply(
                tenant: $tenant,
                conversation: $conversation,
                userId: (string) $lineUserId,
                message: $userMessage
            );

            $reply = $difyResult['answer'] ?: '不好意思，我先幫您轉人工客服處理。';
            $source = 'dify';
        } catch (\Throwable $e) {
            report($e);
            Log::error('Dify reply failed', [
                'tenant_id' => $tenant->id,
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            $reply = '不好意思，目前系統較忙，請稍後再試，或我幫您轉人工客服。';
            $source = 'fallback';
        }

        if ($replyToken) {
            $this->lineMessagingService->reply($replyToken, $reply, $channelAccessToken);
        }

        Message::create([
            'tenant_id' => $tenant->id,
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'direction' => 'outbound',
            'sender_type' => 'ai',
            'message_type' => 'text',
            'content' => $reply,
            'is_ai_generated' => true,
            'reply_source' => $source,
            'sent_at' => now(),
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_ai_reply_at' => now(),
            'ai_enabled' => true,
        ]);
    }
}
