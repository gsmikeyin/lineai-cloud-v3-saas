<?php

namespace App\Http\Controllers;

use App\Models\AuthEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string', 'in:daily,weekly,monthly'],
        ]);

        $period = $validated['period'] ?? 'daily';
        [$start, $authBucketExpression] = $this->periodConfig($period, 'occurred_at');
        [, $userBucketExpression] = $this->periodConfig($period, 'created_at');

        $loginRows = AuthEvent::query()
            ->select([
                DB::raw($authBucketExpression . ' as period'),
                DB::raw('COUNT(DISTINCT user_id) as login_users'),
                DB::raw('COUNT(*) as login_events'),
            ])
            ->where('event_type', AuthEvent::TYPE_LOGIN)
            ->where('occurred_at', '>=', $start)
            ->groupBy(DB::raw($authBucketExpression))
            ->get()
            ->keyBy('period');

        $registerRows = User::query()
            ->select([
                DB::raw($userBucketExpression . ' as period'),
                DB::raw('COUNT(*) as registered_users'),
            ])
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw($userBucketExpression))
            ->get()
            ->keyBy('period');

        $periods = $loginRows->keys()->merge($registerRows->keys())->unique()->sortDesc()->values();

        $rows = $periods->map(function ($periodKey) use ($loginRows, $registerRows) {
            return [
                'period' => $periodKey,
                'login_users' => (int) ($loginRows[$periodKey]->login_users ?? 0),
                'login_events' => (int) ($loginRows[$periodKey]->login_events ?? 0),
                'registered_users' => (int) ($registerRows[$periodKey]->registered_users ?? 0),
            ];
        });

        return response()->json([
            'period' => $period,
            'data' => $rows,
            'summary' => [
                'login_users' => (int) $rows->sum('login_users'),
                'login_events' => (int) $rows->sum('login_events'),
                'registered_users' => (int) $rows->sum('registered_users'),
            ],
        ]);
    }

    public static function record(User $user, string $eventType, string $provider, Request $request): void
    {
        $occurredAt = now();

        try {
            AuthEvent::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'event_type' => $eventType,
                'provider' => $provider,
                'event_date' => $occurredAt->toDateString(),
                'occurred_at' => $occurredAt,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function periodConfig(string $period, string $column): array
    {
        return match ($period) {
            'weekly' => [
                now()->subWeeks(12)->startOfWeek(),
                $this->dateFormatExpression('week', $column),
            ],
            'monthly' => [
                now()->subMonths(12)->startOfMonth(),
                $this->dateFormatExpression('%Y-%m', $column),
            ],
            default => [
                now()->subDays(30)->startOfDay(),
                $this->dateFormatExpression('%Y-%m-%d', $column),
            ],
        };
    }

    protected function dateFormatExpression(string $format, string $column): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => $format === 'week' ? "strftime('%Y-W%W', {$column})" : "strftime('{$format}', {$column})",
            'pgsql' => $format === '%Y-%m-%d'
                ? "to_char({$column}, 'YYYY-MM-DD')"
                : ($format === '%Y-%m' ? "to_char({$column}, 'YYYY-MM')" : "to_char({$column}, 'IYYY-\"W\"IW')"),
            default => $format === 'week' ? "DATE_FORMAT({$column}, '%x-W%v')" : "DATE_FORMAT({$column}, '{$format}')",
        };
    }
}
