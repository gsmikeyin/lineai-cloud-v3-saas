<?php

namespace App\Services\CRM;

use App\Models\Conversation;
use App\Models\User;

class HumanHandoffService
{
    protected array $handoffKeywords = [
        '人工',
        '真人',
        '客服',
        '轉人工',
        '轉接',
        '找人',
        '專人',
    ];

    public function shouldHandoff(string $message): bool
    {
        foreach ($this->handoffKeywords as $keyword) {
            if (mb_stripos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public function enable(Conversation $conversation, ?User $user = null): Conversation
    {
        $conversation->update([
            'human_handoff' => true,
            'ai_enabled' => false,
            'assigned_user_id' => $user?->id ?? $conversation->assigned_user_id,
            'status' => Conversation::STATUS_PENDING,
        ]);

        return $conversation->refresh();
    }

    public function disable(Conversation $conversation): Conversation
    {
        $conversation->update([
            'human_handoff' => false,
            'ai_enabled' => true,
            'status' => Conversation::STATUS_OPEN,
        ]);

        return $conversation->refresh();
    }

    public function assign(Conversation $conversation, User $user): Conversation
    {
        $conversation->update([
            'assigned_user_id' => $user->id,
            'human_handoff' => true,
            'ai_enabled' => false,
            'status' => Conversation::STATUS_PENDING,
        ]);

        return $conversation->refresh();
    }
}