<?php

namespace App\Services\Tenant;

use App\Models\Tenant;

class TenantResolverService
{
    public function resolveByWebhookKey(string $webhookKey): Tenant
    {
        return Tenant::query()
            ->with('lineChannel')
            ->where('webhook_key', $webhookKey)
            ->firstOrFail();
    }
}