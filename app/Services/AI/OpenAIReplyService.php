<?php

namespace App\Services\AI;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class OpenAIReplyService
{
    public function generateReply(Tenant $tenant, string $userMessage, array $context = []): string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model');

        $baseSystemPrompt = $tenant->ai_system_prompt
            ?: '你是商家的 LINE 客服助理，請使用繁體中文、簡潔、友善、專業地回答。若不確定，不要亂猜，請引導轉人工客服。';

        $promptRules = $context['prompt_rules'] ?? [];

        
        $knowledgeText = $this->formatKnowledge($context['knowledge'] ?? []);

        $historyText = $this->formatHistory($context['history'] ?? []);

        $extraPromptRules = $this->formatPromptRules($promptRules);

        $input = <<<TEXT
[Base System Prompt]
{$baseSystemPrompt}

[Prompt Rules]
{$extraPromptRules}

[Knowledge Context]
{$knowledgeText}

[Conversation History]
{$historyText}

[User Message]
{$userMessage}

請直接輸出要回覆給客戶的文字，不要加系統說明、不要加標題、不要解釋你是 AI。
TEXT;

       //直接輸出到console mode
       error_log("generateReply user message input: " . $userMessage);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post(config('services.openai.base_url') . '/responses', [
                'model' => $model,
                'input' => $input,
            ])
            ->throw()
            ->json();

        return trim($response['output'][0]['content'][0]['text'] ?? '不好意思，我先幫您轉人工客服處理。');
    }

    protected function formatKnowledge(array $knowledge): string
    {
        if (empty($knowledge)) {
            return '無';
        }

        return collect($knowledge)
            ->map(fn ($item) => "- {$item}")
            ->implode("\n\n");
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

    protected function formatPromptRules(array $rules): string
    {
        if (empty($rules)) {
            return '無';
        }

        return collect($rules)
            ->map(fn ($rule) => "- {$rule}")
            ->implode("\n");
    }
}