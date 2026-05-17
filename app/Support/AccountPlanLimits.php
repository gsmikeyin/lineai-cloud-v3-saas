<?php

namespace App\Support;

use App\Models\User;

class AccountPlanLimits
{
    public const DAILY_MESSAGES_METRIC = 'daily_messages';

    public static function forRole(?string $role): array
    {
        return match ($role) {
            User::ROLE_BASIC => [
                'max_upload_file_size_kb' => 10 * 1024,
                'max_knowledge_documents' => 2,
                'max_daily_messages' => 1000,
            ],
            User::ROLE_PRO => [
                'max_upload_file_size_kb' => 10 * 1024,
                'max_knowledge_documents' => 5,
                'max_daily_messages' => 5000,
            ],
            User::ROLE_SUPER_ADMIN => [
                'max_upload_file_size_kb' => 10 * 1024,
                'max_knowledge_documents' => 10,
                'max_daily_messages' => null,
            ],
            User::ROLE_ADMIN => [
                'max_upload_file_size_kb' => 10 * 1024,
                'max_knowledge_documents' => 5,
                'max_daily_messages' => null,
            ],
            default => [
                'max_upload_file_size_kb' => 5 * 1024,
                'max_knowledge_documents' => 1,
                'max_daily_messages' => 250,
            ],
        };
    }

    public static function maxUploadFileSizeKb(?string $role): int
    {
        return self::forRole($role)['max_upload_file_size_kb'];
    }

    public static function maxKnowledgeDocuments(?string $role): int
    {
        return self::forRole($role)['max_knowledge_documents'];
    }

    public static function maxDailyMessages(?string $role): ?int
    {
        return self::forRole($role)['max_daily_messages'];
    }
}
