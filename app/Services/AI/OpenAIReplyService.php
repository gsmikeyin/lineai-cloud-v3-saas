<?php

namespace App\Services\AI;

use App\Models\Customer;
use App\Models\KnowledgeItem;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class OpenAIReplyService
{
    public function generateReply(Tenant $tenant, string $userMessage, array $context = []): string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model');

        $systemPrompt = $tenant->ai_system_prompt
            ?: '你是商家的 LINE 客服助理，請用繁體中文、簡潔、友善、專業地回答。若不確定，不要亂猜，請引導轉人工。';

        $knowledgeText = $this->formatKnowledge($context['knowledge'] ?? []);
        $historyText = $this->formatHistory($context['history'] ?? []);

        $input = <<<TEXT
[System Role]
{$systemPrompt}

[Knowledge]
{$knowledgeText}

[Conversation History]
{$historyText}

[User Message]
{$userMessage}

請直接輸出要回覆給客戶的文字，不要加系統說明。
TEXT;

        Log::error("Open AI  input " .  $input );
       
        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post(config('services.openai.base_url') . '/responses', [
                'model' => $model,
                'input' => $input,
            ])
            ->throw()
            ->json();

       
       
        Log::error("Open AI  " .   trim($response['output'][0]['content'][0]['text'] ?? '不好意思，我先幫你轉人工處理。') );


        return trim($response['output'][0]['content'][0]['text'] ?? '不好意思，我先幫你轉人工處理。');
    }

    protected function formatKnowledge(array $knowledge): string
    {
        if (empty($knowledge)) {
            return '無';
        }

        return collect($knowledge)
            ->map(function ($item) {
                return "- {$item}";
            })
            ->implode("\n");
    }

    protected function formatHistory(array $history): string
    {
        if (empty($history)) {
            return '無';
        }

        return collect($history)
            ->map(fn ($row) => "[{$row['role']}] {$row['content']}")
            ->implode("\n");
    }
}
