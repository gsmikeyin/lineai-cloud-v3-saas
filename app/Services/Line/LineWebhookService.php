<?php

namespace App\Services\Line;

use App\Services\AI\OpenAIReplyService;
use App\Services\CRM\ConversationMessageService;
use App\Services\Knowledge\KnowledgeMatcherService;
use App\Services\Tenant\TenantResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CRM\HumanHandoffService;

class LineWebhookService
{   
    
    public function __construct(
        protected LineSignatureService $signatureService,
        protected LineApiService $lineApiService,
        protected TenantResolverService $tenantResolver,
        protected ConversationMessageService $conversationService,
        protected KnowledgeMatcherService $knowledgeMatcher,
        protected OpenAIReplyService $openAIReplyService,
        protected HumanHandoffService $humanHandoffService,
    ) {}

    public function handle(Request $request): void
    {
        $this->signatureService->verify($request);

        $payload = $request->json()->all();
        $events = $payload['events'] ?? [];

        foreach ($events as $event) {
            try {
                $this->handleEvent($event);
            } catch (\Throwable $e) {
                Log::error('LINE webhook event failed', [
                    'error' => $e->getMessage(),
                    'event' => $event,
                ]);

                $replyToken = data_get($event, 'replyToken');
                if ($replyToken) {
                    try {
                        $this->lineApiService->replyText($replyToken, '不好意思，系統忙碌中，請稍後再試或輸入「人工客服」。');
                    } catch (\Throwable $inner) {
                        Log::error('LINE fallback reply failed', [
                            'error' => $inner->getMessage(),
                        ]);
                    }
                }
            }
        }
    }

    protected function handleEvent(array $event): void
   {
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

    $tenant = $this->tenantResolver->resolveFromWebhook($event);

    $profile = $this->lineApiService->getProfile($lineUserId);
    $customer = $this->conversationService->findOrCreateCustomer($tenant, $lineUserId, $profile);
    $customer = $this->conversationService->touchCustomer($customer, $profile);

    $conversation = $this->conversationService->findOrCreateConversation($tenant, $customer);
    $this->conversationService->createInboundMessage($tenant, $conversation, $customer, $event);

    // 客戶要求人工
    if ($this->humanHandoffService->shouldHandoff($userMessage)) {
        $this->humanHandoffService->enable($conversation);

        $reply = '已為您轉接人工客服，請稍候，將由專人為您服務。';

        $this->lineApiService->replyText($replyToken, $reply);
        $this->conversationService->createOutboundAiMessage($tenant, $conversation, $customer, $reply, [
            'reply_source' => 'human_handoff',
        ]);

        return;
    }

    // 若目前已在人工模式，AI 不再自動回
    if ($conversation->human_handoff || !$conversation->ai_enabled) {
        $reply = '您的訊息已送出，人工客服會盡快回覆您。';

        $this->lineApiService->replyText($replyToken, $reply);
        $this->conversationService->createOutboundAiMessage($tenant, $conversation, $customer, $reply, [
            'reply_source' => 'handoff_waiting',
        ]);

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

    $this->lineApiService->replyText($replyToken, $reply);
    $this->conversationService->createOutboundAiMessage($tenant, $conversation, $customer, $reply, [
        'reply_source' => $source,
       ]);
   }
}