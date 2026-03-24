<?php

namespace Database\Seeders;

use App\Models\KnowledgeItem;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DemoKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        if (!$tenant) {
            return;
        }

        KnowledgeItem::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'title' => '營業時間',
            ],
            [
                'type' => 'faq',
                'question' => '你們幾點營業？',
                'answer' => '您好，我們的營業時間為週一到週五 09:00–18:00。',
                'keywords' => ['營業時間', '幾點', '開門', '上班'],
                'status' => 'published',
                'is_ai_enabled' => true,
            ]
        );

        KnowledgeItem::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'title' => '出貨時間',
            ],
            [
                'type' => 'faq',
                'question' => '多久出貨？',
                'answer' => '一般會在付款完成後 1–2 個工作天內安排出貨。',
                'keywords' => ['出貨', '多久', '幾天', '配送'],
                'status' => 'published',
                'is_ai_enabled' => true,
            ]
        );
    }
}