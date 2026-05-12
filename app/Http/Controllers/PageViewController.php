<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PageViewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'page_title' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $viewedAt = now();

        PageView::create([
            'tenant_id' => $user?->tenant_id,
            'user_id' => $user?->id,
            'route_name' => $validated['route_name'] ?? null,
            'page_title' => $validated['page_title'] ?? null,
            'path' => $validated['path'],
            'view_date' => $viewedAt->toDateString(),
            'viewed_at' => $viewedAt,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return response()->json(['success' => true]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string', 'in:daily,weekly,monthly'],
        ]);

        $period = $validated['period'] ?? 'daily';
        [$start, $bucketExpression] = $this->periodConfig($period);

        $rows = PageView::query()
            ->select([
                'path',
                'route_name',
                DB::raw('MAX(page_title) as page_title'),
                DB::raw($bucketExpression . ' as period'),
                DB::raw('COUNT(*) as views'),
            ])
            ->where('viewed_at', '>=', $start)
            ->groupBy('path', 'route_name', DB::raw($bucketExpression))
            ->orderByDesc('period')
            ->orderByDesc('views')
            ->get();

        $summary = PageView::query()
            ->select([
                'path',
                'route_name',
                DB::raw('MAX(page_title) as page_title'),
                DB::raw('COUNT(*) as views'),
            ])
            ->where('viewed_at', '>=', $start)
            ->groupBy('path', 'route_name')
            ->orderByDesc('views')
            ->get();

        return response()->json([
            'period' => $period,
            'data' => $rows,
            'summary' => $summary,
        ]);
    }

    protected function periodConfig(string $period): array
    {
        return match ($period) {
            'weekly' => [
                now()->subWeeks(12)->startOfWeek(),
                $this->dateFormatExpression('week'),
            ],
            'monthly' => [
                now()->subMonths(12)->startOfMonth(),
                $this->dateFormatExpression('%Y-%m'),
            ],
            default => [
                now()->subDays(30)->startOfDay(),
                $this->dateFormatExpression('%Y-%m-%d'),
            ],
        };
    }

    protected function dateFormatExpression(string $format): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => $format === 'week' ? "strftime('%Y-W%W', viewed_at)" : "strftime('{$format}', viewed_at)",
            'pgsql' => $format === '%Y-%m-%d'
                ? "to_char(viewed_at, 'YYYY-MM-DD')"
                : ($format === '%Y-%m' ? "to_char(viewed_at, 'YYYY-MM')" : "to_char(viewed_at, 'IYYY-\"W\"IW')"),
            default => $format === 'week' ? "DATE_FORMAT(viewed_at, '%x-W%v')" : "DATE_FORMAT(viewed_at, '{$format}')",
        };
    }
}
