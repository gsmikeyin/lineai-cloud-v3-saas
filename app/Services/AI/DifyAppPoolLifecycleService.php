<?php

namespace App\Services\AI;

use App\Models\DifyAppPool;
use App\Models\DifyAppPoolAssignment;
use App\Models\TenantAiSetting;
use Illuminate\Support\Facades\DB;

class DifyAppPoolLifecycleService
{
    public function release(DifyAppPool $appPool, ?string $remark = null): void
    {
        DB::transaction(function () use ($appPool, $remark) {
            $tenantId = $appPool->assigned_tenant_id;

            if ($tenantId) {
                TenantAiSetting::where('tenant_id', $tenantId)->update([
                    'dify_app_api_key' => null,
                    'dify_app_name' => null,
                    'dify_app_mode' => 'chat',
                    'is_active' => false,
                ]);

                DifyAppPoolAssignment::create([
                    'dify_app_pool_id' => $appPool->id,
                    'tenant_id' => $tenantId,
                    'action' => 'release',
                    'remark' => $remark,
                ]);
            }

            $appPool->update([
                'status' => 'available',
                'assigned_tenant_id' => null,
            ]);
        });
    }

    public function reassign(DifyAppPool $appPool, int $newTenantId, ?string $remark = null): void
    {
        DB::transaction(function () use ($appPool, $newTenantId, $remark) {
            if ($appPool->assigned_tenant_id) {
                TenantAiSetting::where('tenant_id', $appPool->assigned_tenant_id)->update([
                    'dify_app_api_key' => null,
                    'dify_app_name' => null,
                    'dify_app_mode' => 'chat',
                    'is_active' => false,
                ]);

                DifyAppPoolAssignment::create([
                    'dify_app_pool_id' => $appPool->id,
                    'tenant_id' => $appPool->assigned_tenant_id,
                    'action' => 'release',
                    'remark' => $remark,
                ]);
            }

            TenantAiSetting::updateOrCreate(
                ['tenant_id' => $newTenantId],
                [
                    'provider' => 'dify',
                    'dify_app_api_key' => $appPool->app_api_key,
                    'dify_app_name' => $appPool->app_name,
                    'dify_app_mode' => $appPool->app_mode,
                    'is_active' => true,
                ]
            );

            $appPool->update([
                'status' => 'assigned',
                'assigned_tenant_id' => $newTenantId,
            ]);

            DifyAppPoolAssignment::create([
                'dify_app_pool_id' => $appPool->id,
                'tenant_id' => $newTenantId,
                'action' => 'reassign',
                'remark' => $remark,
            ]);
        });
    }
}
