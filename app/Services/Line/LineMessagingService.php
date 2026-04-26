<?php

namespace App\Services\Line;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class LineMessagingService
{
    public function reply(string $replyToken, string $text, ?string $channelAccessToken): void
    {
        if (!$channelAccessToken) {
            throw new RuntimeException('LINE channel access token missing.');
        }

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $channelAccessToken,
            'Content-Type' => 'application/json',
        ])->post('https://api.line.me/v2/bot/message/reply', [
            'replyToken' => $replyToken,
            'messages' => [[
                'type' => 'text',
                'text' => $text,
            ]],
        ])->throw();
    }

    public function push(string $lineUserId, string $text, ?string $channelAccessToken): void
    {
        if (!$channelAccessToken) {
            throw new RuntimeException('LINE channel access token missing.');
        }

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $channelAccessToken,
            'Content-Type' => 'application/json',
        ])->post('https://api.line.me/v2/bot/message/push', [
            'to' => $lineUserId,
            'messages' => [[
                'type' => 'text',
                'text' => mb_substr($text, 0, 5000),
            ]],
        ])->throw();
    }
}
