<?php

namespace App\Services\Line;

use App\Models\Tenant;
use App\Services\AI\OpenAIReplyService;
use App\Services\CRM\ConversationMessageService;
use App\Services\CRM\HumanHandoffService;
use App\Services\Knowledge\KnowledgeMatcherService;

class LineWebhookEventProcessor
{
    public function __construct(
        protected LineApiService $lineApiService,
        protected ConversationMessageService $conversationService,
        protected KnowledgeMatcherService $knowledgeMatcher,
        protected OpenAIReplyService $openAIReplyService,
        protected HumanHandoffService $humanHandoffService,
    ) {}

    public function process(int $tenantId, array $event): void
    {
        $tenant = Tenant::query()
            ->with('lineChannel')
            ->findOrFail($tenantId);

        $bot = $tenant->lineChannel;

        if (!$bot || !$bot->is_active) {
            return;
        }

        if (($event['type'] ?? null) !== 'message') {
            return;
        }

        if (($event['message']['type'] ?? null) !== 'text') {
            return;
        }

        $lineUserId = data_get($event, 'source.userId');
        $replyToken = data_get($event, 'replyToken');
        $userMessage = trim((string) data_get($event, 'message.text', ''));

        if (!$lineUserId || !$replyToken || $userMessage === '') {
            return;
        }

        $profile = $this->lineApiService->getProfile(
            $lineUserId,
            $bot->channel_access_token
        );

        $customer = $this->conversationService->findOrCreateCustomer(
            tenant: $tenant,
            lineUserId: $lineUserId,
            profile: $profile,
        );

        $customer = $this->conversationService->touchCustomer($customer, $profile);

        $conversation = $this->conversationService->findOrCreateConversation(
            tenant: $tenant,
            customer: $customer,
        );

        $this->conversationService->createInboundMessage(
            $tenant,
            $conversation,
            $customer,
            $event
        );

        if ($this->humanHandoffService->shouldHandoff($userMessage)) {
            $this->humanHandoffService->enable($conversation);

            $reply = '已為您轉接人工客服，請稍候，將由專人為您服務。';

            $this->lineApiService->replyText(
                $replyToken,
                $reply,
                $bot->channel_access_token
            );

            $this->conversationService->createOutboundAiMessage(
                $tenant,
                $conversation,
                $customer,
                $reply,
                ['reply_source' => 'human_handoff']
            );

            return;
        }

        if ($conversation->human_handoff || !$conversation->ai_enabled) {
            $reply = '您的訊息已送出，人工客服會盡快回覆您。';

            $this->lineApiService->replyText(
                $replyToken,
                $reply,
                $bot->channel_access_token
            );

            $this->conversationService->createOutboundAiMessage(
                $tenant,
                $conversation,
                $customer,
                $reply,
                ['reply_source' => 'handoff_waiting']
            );

            return;
        }

        $directAnswer = $this->knowledgeMatcher->findDirectAnswer($tenant, $userMessage);

        if ($directAnswer) {
            $reply = $directAnswer;
            $source = 'knowledge';
        } else {
            $history = $conversation->messages()
                ->latest('id')
                ->limit(6)
                ->get()
                ->reverse()
                ->map(function ($msg) {
                    return [
                        'role' => $msg->direction === 'inbound' ? 'user' : 'assistant',
                        'content' => $msg->content,
                    ];
                })
                ->values()
                ->all();

            $knowledge = $this->knowledgeMatcher->getKnowledgeContext($tenant);

            $reply = $this->openAIReplyService->generateReply($tenant, $userMessage, [
                'history' => $history,
                'knowledge' => $knowledge,
            ]);
            $source = 'ai';
        }

        $this->lineApiService->replyText(
            $replyToken,
            $reply,
            $bot->channel_access_token
        );

        $this->conversationService->createOutboundAiMessage(
            $tenant,
            $conversation,
            $customer,
            $reply,
            ['reply_source' => $source]
        );
    }
}