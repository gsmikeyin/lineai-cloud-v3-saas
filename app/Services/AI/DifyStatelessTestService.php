<?php

namespace App\Services\AI;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DifyStatelessTestService
{
    public function test(Tenant $tenant, string $userId, string $message): array
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
                'channel' => 'debug',
            ],
            'query' => $message,
            'response_mode' => 'blocking',
            'user' => (string) $userId,
        ];

        $httpResponse = Http::timeout(90)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $settings->dify_app_api_key,
                'Content-Type' => 'application/json',
            ])->post(
                rtrim(config('services.dify.base_url'), '/') . '/chat-messages',
                $payload
            );

        if ($httpResponse->failed()) {
            throw new RuntimeException(
                'Dify test failed: ' . $httpResponse->body()
            );
        }

        $response = $httpResponse->json();

        return [
            'answer' => data_get($response, 'answer'),
            'conversation_id' => data_get($response, 'conversation_id'),
            'raw' => $response,
        ];
    }
}