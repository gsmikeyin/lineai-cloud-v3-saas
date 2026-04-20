<?php

namespace App\Services\AI;

use App\Models\Conversation;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DifyChatService
{
    public function reply(
        Tenant $tenant,
        Conversation $conversation,
        string $userId,
        string $message
    ): array {
        $settings = $tenant->aiSetting;

        if (!$settings || !$settings->is_active) {
            throw new RuntimeException('Dify is not enabled for this tenant.');
        }

        if (empty($settings->dify_app_api_key)) {
            throw new RuntimeException('Dify App API Key not configured.');
        }

        $payload = [
            'inputs' => new \stdClass(),
            'query' => $message,
            'response_mode' => 'blocking',
            'user' => (string) $userId,
        ];

        if (!empty($conversation->external_conversation_id)) {
            $payload['conversation_id'] = $conversation->external_conversation_id;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $settings->dify_app_api_key,
            'Content-Type' => 'application/json',
        ])->post(
            rtrim($settings->dify_base_url, '/') . '/chat-messages',
            $payload
        )->throw()->json();

        $conversationId = data_get($response, 'conversation_id');
        $answer = trim((string) data_get($response, 'answer', ''));

        if ($conversationId && $conversation->external_conversation_id !== $conversationId) {
            $conversation->update([
                'external_conversation_id' => $conversationId,
            ]);
        }

        return [
            'answer' => $answer,
            'conversation_id' => $conversationId,
            'raw' => $response,
        ];
    }
}
