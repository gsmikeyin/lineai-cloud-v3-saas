<?php

namespace App\Services\Line;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Tenant;
use App\Services\AI\DifyChatService;
use App\Services\Billing\UsageLimitService;
use App\Support\AccountPlanLimits;
use Illuminate\Support\Facades\Log;

class LineWebhookEventProcessor
{
    public function __construct(
        protected DifyChatService $difyChatService,
        protected LineMessagingService $lineMessagingService,
        protected UsageLimitService $usageLimitService,
    ) {}

    public function process(int $tenantId, array $event): void
    {
        if (data_get($event, 'type') !== 'message' || data_get($event, 'message.type') !== 'text') {
            return;
        }

        $tenant = Tenant::query()->with(['lineChannel', 'owner'])->findOrFail($tenantId);
        $lineUserId = data_get($event, 'source.userId');
        $userMessage = trim((string) data_get($event, 'message.text', ''));

        if (!$lineUserId || $userMessage === '') {
            return;
        }

        $customer = Customer::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'line_user_id' => $lineUserId,
            ],
            [
                'source' => 'line',
                'display_name' => 'LINE User',
                'status' => 'active',
                'first_interaction_at' => now(),
            ]
        );

        $customer->update([
            'last_interaction_at' => now(),
            'total_messages' => (int) $customer->total_messages + 1,
        ]);

        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'status' => Conversation::STATUS_OPEN,
            ],
            [
                'channel' => 'line',
                'priority' => Conversation::PRIORITY_NORMAL,
                'ai_enabled' => true,
                'human_handoff' => false,
                'last_message_at' => now(),
            ]
        );

        $this->processTextMessage($tenant, $event, $customer->fresh(), $conversation, $userMessage);
    }

    public function processTextMessage(Tenant $tenant, array $event, Customer $customer, Conversation $conversation, string $userMessage): void
    {
        $replyToken = data_get($event, 'replyToken');
        $lineUserId = $customer->line_user_id;
        $channelAccessToken = $tenant->lineChannel?->channel_access_token;

        Message::create([
            'tenant_id' => $tenant->id,
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'direction' => Message::DIRECTION_INBOUND,
            'sender_type' => Message::SENDER_CUSTOMER,
            'message_type' => Message::TYPE_TEXT,
            'content' => $userMessage,
            'line_message_id' => data_get($event, 'message.id'),
            'reply_token' => $replyToken,
            'raw_payload' => $event,
            'is_ai_generated' => false,
            'sent_at' => now(),
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_customer_message_at' => now(),
            'unread_count' => ($conversation->unread_count ?? 0) + 1,
        ]);

        if ($conversation->human_handoff) {
            if ($replyToken) {
                $this->lineMessagingService->reply(
                    $replyToken,
                    '已轉交人工客服，請稍候。',
                    $channelAccessToken
                );
            }

            return;
        }

        $dailyLimit = AccountPlanLimits::maxDailyMessages($tenant->owner?->role);
        if (! $this->usageLimitService->withinDailyLimit($tenant, AccountPlanLimits::DAILY_MESSAGES_METRIC, $dailyLimit)) {
            $limitText = '今日訊息數已達方案上限，請聯絡客服升級方案或明日再試。';

            if ($replyToken) {
                $this->lineMessagingService->reply($replyToken, $limitText, $channelAccessToken);
            }

            Message::create([
                'tenant_id' => $tenant->id,
                'conversation_id' => $conversation->id,
                'customer_id' => $customer->id,
                'direction' => Message::DIRECTION_OUTBOUND,
                'sender_type' => Message::SENDER_SYSTEM,
                'message_type' => Message::TYPE_TEXT,
                'content' => $limitText,
                'is_ai_generated' => false,
                'meta' => [
                    'reply_source' => 'daily_limit',
                    'daily_limit' => $dailyLimit,
                ],
                'sent_at' => now(),
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'last_agent_reply_at' => now(),
            ]);

            return;
        }

        $this->usageLimitService->incrementDaily($tenant, AccountPlanLimits::DAILY_MESSAGES_METRIC);

        try {
            $difyResult = $this->difyChatService->reply(
                tenant: $tenant,
                conversation: $conversation,
                userId: (string) $lineUserId,
                message: $userMessage
            );

            $reply = $difyResult['answer'] ?: '目前無法產生回覆，請稍後再試。';
            $source = 'dify';
        } catch (\Throwable $e) {
            report($e);
            Log::error('Dify reply failed', [
                'tenant_id' => $tenant->id,
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            $reply = '目前系統忙碌，請稍後再試。';
            $source = 'fallback';
        }

        if ($replyToken) {
            $this->lineMessagingService->reply($replyToken, $reply, $channelAccessToken);
        }

        Message::create([
            'tenant_id' => $tenant->id,
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'direction' => Message::DIRECTION_OUTBOUND,
            'sender_type' => Message::SENDER_AI,
            'message_type' => Message::TYPE_TEXT,
            'content' => $reply,
            'is_ai_generated' => true,
            'meta' => ['reply_source' => $source],
            'sent_at' => now(),
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_agent_reply_at' => now(),
            'ai_enabled' => true,
        ]);
    }
}
