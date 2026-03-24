<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\UsageLog;

class UsageLimitService
{
    public function increment(Tenant $tenant, string $metric, float $value = 1): UsageLog
    {
        $periodDate = now()->startOfMonth()->toDateString();

        $log = UsageLog::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'metric' => $metric,
                'period_date' => $periodDate,
                'period_type' => 'monthly',
            ],
            ['value' => 0]
        );

        $log->value += $value;
        $log->save();

        return $log;
    }

    public function withinLimit(Tenant $tenant, string $metric): bool
    {
        $plan = $tenant->plan;
        if (! $plan instanceof Plan) {
            return true;
        }

        $limitMap = [
            'ai_replies' => $plan->max_monthly_ai_replies,
            'broadcasts' => $plan->max_monthly_broadcasts,
        ];

        $limit = $limitMap[$metric] ?? null;
        if (! $limit) {
            return true;
        }

        $current = UsageLog::query()
            ->where('tenant_id', $tenant->id)
            ->where('metric', $metric)
            ->where('period_date', now()->startOfMonth()->toDateString())
            ->value('value');

        return (float) ($current ?? 0) < (float) $limit;
    }
}
