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

        $lineText = $this->formatMarkdownForLine($text);

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $channelAccessToken,
            'Content-Type' => 'application/json',
        ])->post('https://api.line.me/v2/bot/message/reply', [
            'replyToken' => $replyToken,
            'messages' => [[
                'type' => 'text',
                'text' => mb_substr($lineText, 0, 5000),
            ]],
        ])->throw();
    }

    public function push(string $lineUserId, string $text, ?string $channelAccessToken): void
    {
        if (!$channelAccessToken) {
            throw new RuntimeException('LINE channel access token missing.');
        }

        $lineText = $this->formatMarkdownForLine($text);

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $channelAccessToken,
            'Content-Type' => 'application/json',
        ])->post('https://api.line.me/v2/bot/message/push', [
            'to' => $lineUserId,
            'messages' => [[
                'type' => 'text',
                'text' => mb_substr($lineText, 0, 5000),
            ]],
        ])->throw();
    }

    protected function formatMarkdownForLine(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $text = preg_replace_callback('/```(?:[a-zA-Z0-9_-]+)?\n?([\s\S]*?)```/', function ($matches) {
            return "\n" . trim($matches[1]) . "\n";
        }, $text) ?? $text;

        $text = preg_replace('/^#{1,6}\s*(.+)$/m', '$1', $text) ?? $text;
        $text = preg_replace('/^\s*[-*]\s+/m', '• ', $text) ?? $text;
        $text = preg_replace('/^\s*(\d+)\.\s+/m', '$1. ', $text) ?? $text;
        $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text) ?? $text;
        $text = preg_replace('/__([^_]+)__/', '$1', $text) ?? $text;
        $text = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/', '$1', $text) ?? $text;
        $text = preg_replace('/(?<!_)_([^_\n]+)_(?!_)/', '$1', $text) ?? $text;
        $text = preg_replace('/`([^`]+)`/', '$1', $text) ?? $text;
        $text = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '$1 $2', $text) ?? $text;
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1: $2', $text) ?? $text;
        $text = preg_replace('/^[>\s]+(.+)$/m', '$1', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
