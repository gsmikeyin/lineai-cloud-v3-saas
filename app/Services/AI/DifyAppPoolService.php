<?php

namespace App\Services\AI;

use App\Models\DifyAppPool;
use RuntimeException;

class DifyAppPoolService
{
    public function assignAvailablePoolToTenant(int $tenantId): DifyAppPool
    {
        $pool = DifyAppPool::query()->where('status', 'available')->orderBy('id')->first();

        if (!$pool) {
            throw new RuntimeException('No available Dify App Pool found.');
        }

        $pool->update([
            'status' => 'assigned',
            'assigned_tenant_id' => $tenantId,
        ]);

        return $pool->fresh();
    }
}
