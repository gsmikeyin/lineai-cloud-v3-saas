<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeItem;
use App\Models\Tenant;

class KnowledgeMatcherService
{
    public function findDirectAnswer(Tenant $tenant, string $message): ?string
    {
        $match = $this->analyzeMatch($tenant, $message);

        if (!$match['matched']) {
            return null;
        }

        return $match['item']['answer'] ?: $match['item']['content'];
    }

    public function analyzeMatch(Tenant $tenant, string $message): array
    {
        $message = trim($message);

        if ($message === '') {
            return [
                'matched' => false,
                'score' => 0,
                'matched_keywords' => [],
                'item' => null,
                'candidates' => [],
            ];
        }

        $items = $tenant->knowledgeItems()
            ->where('status', KnowledgeItem::STATUS_PUBLISHED)
            ->where('is_ai_enabled', true)
            ->whereIn('type', [
                KnowledgeItem::TYPE_FAQ,
                KnowledgeItem::TYPE_PRODUCT,
                KnowledgeItem::TYPE_POLICY,
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $bestItem = null;
        $bestScore = 0;
        $bestMatchedKeywords = [];
        $candidates = [];

        foreach ($items as $item) {
            $result = $this->calculateKeywordScore($message, $item->keywords ?? []);

            if ($result['score'] > 0) {
                $candidates[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'score' => $result['score'],
                    'matched_keywords' => $result['matched_keywords'],
                    'sort_order' => $item->sort_order,
                    'answer' => $item->answer,
                    'content' => $item->content,
                ];
            }

            if ($result['score'] > $bestScore) {
                $bestItem = $item;
                $bestScore = $result['score'];
                $bestMatchedKeywords = $result['matched_keywords'];
            }
        }

        usort($candidates, function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
            }

            return $b['score'] <=> $a['score'];
        });

        return [
            'matched' => $bestItem !== null && $bestScore > 0,
            'score' => $bestScore,
            'matched_keywords' => $bestMatchedKeywords,
            'item' => $bestItem ? [
                'id' => $bestItem->id,
                'title' => $bestItem->title,
                'type' => $bestItem->type,
                'question' => $bestItem->question,
                'answer' => $bestItem->answer,
                'content' => $bestItem->content,
                'sort_order' => $bestItem->sort_order,
                'keywords' => $bestItem->keywords ?? [],
            ] : null,
            'candidates' => $candidates,
        ];
    }

    public function getKnowledgeContext(Tenant $tenant, int $limit = 8): array
    {
        return $tenant->knowledgeItems()
            ->where('status', KnowledgeItem::STATUS_PUBLISHED)
            ->where('is_ai_enabled', true)
            ->whereIn('type', [
                KnowledgeItem::TYPE_FAQ,
                KnowledgeItem::TYPE_PRODUCT,
                KnowledgeItem::TYPE_POLICY,
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $parts = [];

                $parts[] = "[{$item->type}] {$item->title}";

                if (!empty($item->question)) {
                    $parts[] = "問題：{$item->question}";
                }

                if (!empty($item->answer)) {
                    $parts[] = "答案：{$item->answer}";
                }

                if (!empty($item->content)) {
                    $parts[] = "補充：{$item->content}";
                }

                return implode("\n", $parts);
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getPromptRules(Tenant $tenant): array
    {
        return $tenant->knowledgeItems()
            ->where('status', KnowledgeItem::STATUS_PUBLISHED)
            ->where('is_ai_enabled', true)
            ->where('type', KnowledgeItem::TYPE_PROMPT)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return trim($item->content ?: $item->answer ?: $item->title);
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function calculateKeywordScore(string $message, array $keywords): array
    {
        $score = 0;
        $matchedKeywords = [];

        foreach ($keywords as $keyword) {
            $keyword = trim((string) $keyword);

            if ($keyword === '') {
                continue;
            }

            if (mb_stripos($message, $keyword) !== false) {
                $score += mb_strlen($keyword);
                $matchedKeywords[] = $keyword;
            }
        }

        return [
            'score' => $score,
            'matched_keywords' => array_values(array_unique($matchedKeywords)),
        ];
    }
}