<?php

namespace App\Http\Controllers;

use App\Services\AI\DifyStatelessTestService;
use Illuminate\Http\Request;

class DifyTestController extends Controller
{
    public function __construct(
        protected DifyStatelessTestService $difyStatelessTestService
    ) {}

    public function test(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for current user.',
            ], 422);
        }

        if (!$tenant->aiSetting || empty($tenant->aiSetting->dify_dataset_id)) {
            return response()->json([
                'message' => 'This tenant has no Dify dataset configured.',
            ], 422);
        }

        $result = $this->difyStatelessTestService->test(
            tenant: $tenant,
            userId: 'debug-user-' . $user->id,
            message: $validated['message']
        );

        $record = [
            'message' => $validated['message'],
            'answer' => $result['answer'] ?? null,
            'conversation_id' => $result['conversation_id'] ?? null,
            'raw' => $result['raw'] ?? [],
            'tested_at' => now()->toISOString(),
            'tested_by' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];

        $settings = $tenant->settings ?? [];
        $settings['dify_last_test'] = $record;
        $tenant->forceFill(['settings' => $settings])->save();

        return response()->json($record);
    }

    public function last(Request $request)
    {
        $tenant = $request->user()?->tenant;

        if (! $tenant) {
            return response()->json([
                'message' => 'Tenant not found for current user.',
            ], 422);
        }

        return response()->json([
            'data' => data_get($tenant->settings ?? [], 'dify_last_test'),
        ]);
    }
}
