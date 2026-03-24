<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\KnowledgeItem;
use App\Models\Message;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantLineChannel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::where('code', 'growth')->firstOrFail();

        $tenant = Tenant::updateOrCreate(
            ['slug' => 'demo-store'],
            [
                'plan_id' => $plan->id,
                'name' => 'Demo Store',
                'company_name' => 'Demo Store Co.',
                'status' => 'active',
                'contact_name' => 'Owner',
                'contact_email' => 'owner@example.com',
                'timezone' => 'Asia/Taipei',
                'ai_system_prompt' => '你是 Demo Store 的 LINE 客服助理。請用繁體中文回答，簡潔、親切、實用。',
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'status' => 'active',
            ]
        );

        $tenant->owner_user_id = $user->id;
        $tenant->save();

        TenantLineChannel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'provider' => 'line'],
            [
                'channel_name' => 'Demo LINE OA',
                'channel_id' => 'demo-channel-id',
                'channel_secret' => 'demo-secret',
                'channel_access_token' => 'demo-token',
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        KnowledgeItem::updateOrCreate(
            ['tenant_id' => $tenant->id, 'title' => '營業時間'],
            [
                'type' => 'faq',
                'question' => '請問營業時間？',
                'answer' => '我們每天 10:00 到 21:00 營業。',
                'status' => 'published',
                'is_ai_enabled' => true,
            ]
        );

        $customer = Customer::updateOrCreate(
            ['tenant_id' => $tenant->id, 'line_user_id' => 'Udemo123'],
            [
                'display_name' => '王小明',
                'source' => 'line',
                'status' => 'active',
                'first_interaction_at' => now(),
                'last_interaction_at' => now(),
                'total_messages' => 2,
            ]
        );

        $conversation = Conversation::updateOrCreate(
            ['tenant_id' => $tenant->id, 'customer_id' => $customer->id, 'status' => 'open'],
            [
                'channel' => 'line',
                'priority' => 'normal',
                'ai_enabled' => true,
                'last_message_at' => now(),
            ]
        );

        Message::updateOrCreate(
            ['tenant_id' => $tenant->id, 'conversation_id' => $conversation->id, 'direction' => 'inbound', 'content' => '請問今天有營業嗎？'],
            [
                'customer_id' => $customer->id,
                'sender_type' => 'customer',
                'message_type' => 'text',
                'sent_at' => now()->subMinute(),
            ]
        );

        Message::updateOrCreate(
            ['tenant_id' => $tenant->id, 'conversation_id' => $conversation->id, 'direction' => 'outbound', 'content' => '有的，我們今天 10:00 到 21:00 營業。'],
            [
                'customer_id' => $customer->id,
                'sender_type' => 'ai',
                'message_type' => 'text',
                'is_ai_generated' => true,
                'sent_at' => now(),
            ]
        );
    }
}
