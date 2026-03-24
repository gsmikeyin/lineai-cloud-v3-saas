<?php

namespace App\Services\Line;

use Illuminate\Support\Facades\Http;

class LineMessagingService
{
    public function reply(string $replyToken, string $text, ?string $accessToken = null): array
    {
        $token = $accessToken ?: (string) config('services.line.channel_access_token');

        $response = Http::withToken($token)
            ->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages' => [
                    ['type' => 'text', 'text' => $text],
                ],
            ]);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json(),
        ];
    }

    public function push(string $userId, string $text, ?string $accessToken = null): array
    {
        $token = $accessToken ?: (string) config('services.line.channel_access_token');

        $response = Http::withToken($token)
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $userId,
                'messages' => [
                    ['type' => 'text', 'text' => $text],
                ],
            ]);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json(),
        ];
    }
}
