<?php

namespace App\Http\Controllers;

use App\Services\AI\DifyKnowledgeService;
use Illuminate\Http\Request;

class KnowledgeUploadController extends Controller
{
    public function __construct(
        protected DifyKnowledgeService $difyKnowledgeService
    ) {}

    public function upload(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for current user.',
            ], 422);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,txt,doc,docx,md'],
        ]);

        $record = $this->difyKnowledgeService->uploadDocument(
            tenant: $tenant,
            file: $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data' => $record,
        ], 201);
    }

    public function index(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for current user.',
            ], 422);
        }

        return response()->json(
            $tenant->knowledgeSources()->latest('id')->paginate(20)
        );
    }
}