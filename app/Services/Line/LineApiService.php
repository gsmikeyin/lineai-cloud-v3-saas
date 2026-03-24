<?php

namespace App\Services\Line;

use Illuminate\Support\Facades\Http;

class LineApiService
{
    public function replyText(string $replyToken, string $text, ?string $accessToken = null): void
    {
        $token = $accessToken ?: config('services.line.channel_access_token');

        Http::withToken($token)
            ->acceptJson()
            ->post(config('services.line.base_url') . '/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => mb_substr($text, 0, 5000),
                    ],
                ],
            ])
            ->throw();
    }

    public function pushText(string $userId, string $text, ?string $accessToken = null): void
    {
        $token = $accessToken ?: config('services.line.channel_access_token');

        Http::withToken($token)
            ->acceptJson()
            ->post(config('services.line.base_url') . '/v2/bot/message/push', [
                'to' => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => mb_substr($text, 0, 5000),
                    ],
                ],
            ])
            ->throw();
    }

    public function getProfile(string $userId, ?string $accessToken = null): ?array
    {
        $token = $accessToken ?: config('services.line.channel_access_token');

        $response = Http::withToken($token)
            ->acceptJson()
            ->get(config('services.line.base_url') . "/v2/bot/profile/{$userId}");

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }
}