<?php

namespace App\Services\Knowledge;

use App\Models\Tenant;

class KnowledgeMatcherService
{
    public function findDirectAnswer(Tenant $tenant, string $message): ?string
    {
        $items = $tenant->knowledgeItems()
            ->where('status', 'published')
            ->where('is_ai_enabled', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($items as $item) {
            $keywords = $item->keywords ?? [];

            foreach ($keywords as $keyword) {
                if ($keyword !== '' && mb_stripos($message, $keyword) !== false) {
                    return $item->answer ?: $item->content;
                }
            }
        }

        return null;
    }

    public function getKnowledgeContext(Tenant $tenant, int $limit = 8): array
    {
        return $tenant->knowledgeItems()
            ->where('status', 'published')
            ->where('is_ai_enabled', true)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return trim(($item->title ?: 'Knowledge') . '：' . ($item->answer ?: $item->content ?: ''));
            })
            ->filter()
            ->values()
            ->all();
    }
}