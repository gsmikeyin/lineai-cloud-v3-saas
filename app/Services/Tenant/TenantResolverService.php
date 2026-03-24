<?php

namespace App\Services\Tenant;

use App\Models\Tenant;

class TenantResolverService
{
    public function resolveFromWebhook(array $event): Tenant
    {
        return Tenant::query()->firstOrFail();
    }
}