<?php

namespace Database\Seeders;

use App\Models\KnowledgeItem;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class KnowledgeItemSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::query()->get();

        foreach ($tenants as $tenant) {
            $this->seedTenantKnowledge($tenant->id);
        }
    }

    protected function seedTenantKnowledge(int $tenantId): void
    {
        $items = [
            [
                'type' => 'faq',
                'title' => '營業時間',
                'question' => '你們幾點營業？',
                'answer' => '您好，我們的營業時間為週一到週五 09:00–18:00，國定假日依公告為準。',
                'content' => '若您需要非營業時間協助，請先留言，客服上班後會盡快回覆。',
                'status' => 'published',
                'sort_order' => 1,
                'is_ai_enabled' => true,
                'keywords' => ['營業時間', '幾點', '上班', '開門'],
            ],
            [
                'type' => 'faq',
                'title' => '出貨時間',
                'question' => '多久會出貨？',
                'answer' => '一般會在付款完成後 1–2 個工作天內安排出貨。',
                'content' => '若遇到促銷檔期或缺貨，實際出貨時間可能略有延長。',
                'status' => 'published',
                'sort_order' => 2,
                'is_ai_enabled' => true,
                'keywords' => ['出貨', '多久', '幾天', '配送'],
            ],
            [
                'type' => 'policy',
                'title' => '退換貨政策',
                'question' => '可以退貨嗎？',
                'answer' => '依消費者保護法規定，您可在收到商品後 7 天內提出退貨申請。',
                'content' => '商品需保持完整，若屬個人衛生用品或特殊商品，將依頁面說明辦理。',
                'status' => 'published',
                'sort_order' => 3,
                'is_ai_enabled' => true,
                'keywords' => ['退貨', '退款', '退換貨', '取消訂單'],
            ],
            [
                'type' => 'product',
                'title' => '產品特色說明',
                'question' => '這個產品有什麼特色？',
                'answer' => '本產品主打穩定、易用與高效率，適合中小企業快速導入。',
                'content' => '可搭配 CRM、AI 自動回覆與客服後台一起使用。',
                'status' => 'published',
                'sort_order' => 4,
                'is_ai_enabled' => true,
                'keywords' => ['特色', '功能', '優勢', '差異'],
            ],
            [
                'type' => 'prompt',
                'title' => 'AI 回覆規則',
                'question' => null,
                'answer' => null,
                'content' => '請使用繁體中文、簡潔、友善、專業地回答。若不確定，不要亂猜，應引導客戶轉人工客服。',
                'status' => 'published',
                'sort_order' => 5,
                'is_ai_enabled' => true,
                'keywords' => [],
            ],
        ];

        foreach ($items as $item) {
            KnowledgeItem::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'title' => $item['title'],
                ],
                $item + ['tenant_id' => $tenantId]
            );
        }
    }
}