<?php

namespace App\Services\AI;

use App\Models\Conversation;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DifyChatService
{
    public function reply(Tenant $tenant, Conversation $conversation, string $userId, string $message): array
    {
        $settings = $tenant->aiSetting;

        if (!$settings || !$settings->is_active) {
            throw new RuntimeException('Dify is not enabled for this tenant.');
        }

        if (empty($settings->dify_app_api_key)) {
            throw new RuntimeException('Dify App API Key not configured.');
        }

        $payload = [
            'inputs' => [
                'tenant_id' => (string) $tenant->id,
                'dataset_id' => (string) $settings->dify_dataset_id,
                'brand_name' => (string) $tenant->name,
                'locale' => (string) ($tenant->locale ?: 'zh_TW'),
                'channel' => 'line',
            ],
            'query' => $message,
            'response_mode' => 'blocking',
            'user' => (string) $userId,
        ];

        if (!empty($conversation->external_conversation_id)) {
            $payload['conversation_id'] = $conversation->external_conversation_id;
        }

        Log::info('Dify request', [
            'tenant_id' => $tenant->id,
            'conversation_id' => $conversation->id,
        ]);

        $response = Http::timeout(90)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $settings->dify_app_api_key,
                'Content-Type' => 'application/json',
            ])->post(rtrim(config('services.dify.base_url'), '/') . '/chat-messages', $payload)
            ->throw()
            ->json();

        $externalId = data_get($response, 'conversation_id');
        $answer = trim((string) data_get($response, 'answer', ''));

        if ($externalId && $conversation->external_conversation_id !== $externalId) {
            $conversation->update([
                'external_conversation_id' => $externalId,
            ]);
        }

        return [
            'answer' => $answer,
            'conversation_id' => $externalId,
            'raw' => $response,
        ];
    }
}
