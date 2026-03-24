<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['slug' => 'demo-tenant'],
            [
                'name' => 'Demo Tenant',
                'webhook_key' => 'demoWebhookKey1234567890',
                'status' => 'active',
                'timezone' => 'Asia/Taipei',
                'locale' => 'zh_TW',
                'currency' => 'TWD',
            ]
        );
    }
}