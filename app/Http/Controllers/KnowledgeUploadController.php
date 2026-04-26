<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeSource;
use App\Services\AI\DifyKnowledgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class KnowledgeUploadController extends Controller
{
    private const MAX_DOCUMENTS_PER_TENANT = 2;

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

        if ($tenant->knowledgeSources()->count() >= self::MAX_DOCUMENTS_PER_TENANT) {
            return response()->json([
                'message' => 'Knowledge document limit reached. You can upload up to 2 files.',
            ], 422);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,txt,doc,docx,md'],
        ]);

        $file = $request->file('file');
        $alreadyExists = $tenant->knowledgeSources()
            ->where('name', $file->getClientOriginalName())
            ->where('file_size', $file->getSize())
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'This knowledge document has already been uploaded.',
            ], 422);
        }

        $record = $this->difyKnowledgeService->uploadDocument(
            tenant: $tenant,
            file: $file
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

        Log::error("user = " . $user);
        Log::error("tenant = " . $tenant);


        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for current user.',
            ], 422);
        }

        return response()->json(
            $tenant->knowledgeSources()->latest('id')->paginate(self::MAX_DOCUMENTS_PER_TENANT)
        );
    }

    public function destroy(Request $request, KnowledgeSource $knowledgeSource)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ((int) $knowledgeSource->tenant_id !== (int) $user->tenant_id) {
            abort(403);
        }

        $this->difyKnowledgeService->deleteDocument($knowledgeSource);

        return response()->json([
            'success' => true,
        ]);
    }
}
