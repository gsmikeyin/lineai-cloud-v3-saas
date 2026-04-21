<?php

namespace App\Http\Controllers;

use App\Models\DifyAppPool;
use App\Models\DifyAppPoolAssignment;
use App\Services\AI\DifyAppPoolLifecycleService;
use Illuminate\Http\Request;

class DifyAppPoolUiController extends Controller
{
    public function __construct(
        protected DifyAppPoolLifecycleService $lifecycleService
    ) {}

    public function index()
    {
        return response()->json([
            'data' => DifyAppPool::query()->latest('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_api_key' => ['required', 'string'],
            'app_mode' => ['required', 'in:chat,workflow'],
        ]);

        $pool = DifyAppPool::create([
            'app_name' => $validated['app_name'],
            'app_api_key' => $validated['app_api_key'],
            'app_mode' => $validated['app_mode'],
            'status' => 'available',
        ]);

        return response()->json([
            'success' => true,
            'data' => $pool,
        ], 201);
    }

    public function assignments(DifyAppPool $difyAppPool)
    {
        return response()->json([
            'data' => DifyAppPoolAssignment::query()
                ->where('dify_app_pool_id', $difyAppPool->id)
                ->latest('id')
                ->get(),
        ]);
    }

    public function release(Request $request, DifyAppPool $difyAppPool)
    {
        $validated = $request->validate([
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        $this->lifecycleService->release($difyAppPool, $validated['remark'] ?? null);
        return response()->json(['success' => true]);
    }

    public function reassign(Request $request, DifyAppPool $difyAppPool)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        $this->lifecycleService->reassign($difyAppPool, (int) $validated['tenant_id'], $validated['remark'] ?? null);
        return response()->json(['success' => true]);
    }
}
