<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeSource;
use App\Models\User;
use App\Services\AI\DifyKnowledgeService;
use App\Support\AccountPlanLimits;
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

        $maxDocuments = $this->maxDocumentsForUser($user);
        $maxFileSizeKb = $this->maxFileSizeKbForUser($user);

        if ($tenant->knowledgeSources()->count() >= $maxDocuments) {
            return response()->json([
                'message' => "Knowledge document limit reached. You can upload up to {$maxDocuments} files.",
            ], 422);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,txt,doc,docx,md', 'max:' . $maxFileSizeKb],
        ], [
            'file.max' => 'Each knowledge document must not be larger than ' . $this->formatMb($maxFileSizeKb) . '.',
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

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for current user.',
            ], 422);
        }

        $maxDocuments = $this->maxDocumentsForUser($user);

        $documents = $tenant->knowledgeSources()
            ->latest('id')
            ->paginate($maxDocuments)
            ->toArray();

        return response()->json([
            ...$documents,
            'max_documents' => $maxDocuments,
            'max_file_size_kb' => $this->maxFileSizeKbForUser($user),
        ]);
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

    private function maxDocumentsForUser(User $user): int
    {
        return AccountPlanLimits::maxKnowledgeDocuments($user->role);
    }

    private function maxFileSizeKbForUser(User $user): int
    {
        return AccountPlanLimits::maxUploadFileSizeKb($user->role);
    }

    private function formatMb(int $kilobytes): string
    {
        return rtrim(rtrim(number_format($kilobytes / 1024, 1), '0'), '.') . 'MB';
    }
}
