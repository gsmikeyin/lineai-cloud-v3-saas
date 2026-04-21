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

        return response()->json([
            'message' => $validated['message'],
            'answer' => $result['answer'] ?? null,
            'conversation_id' => $result['conversation_id'] ?? null,
            'raw' => $result['raw'] ?? [],
        ]);
    }
}