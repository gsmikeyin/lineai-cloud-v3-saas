<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\KnowledgeUploadService;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class KnowledgeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        return response()->json(
            KnowledgeItem::tenant($tenantId)->latest()->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'question' => ['nullable', 'string'],
            'answer' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $data['tenant_id'] = $request->user()->tenant_id;
        $data['status'] = 'published';
        $data['is_ai_enabled'] = true;

        return response()->json(KnowledgeItem::create($data), 201);
    }


    public function upload(Request $request, KnowledgeUploadService $service)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50MB
            'vector_store_id' => ['nullable', 'string'],
            'vector_store_name' => ['nullable', 'string', 'max:255'],
            'chunking' => ['nullable', 'in:auto,static'],
            'max_chunk_size_tokens' => ['nullable', 'integer', 'min:100', 'max:4096'],
            'chunk_overlap_tokens' => ['nullable', 'integer', 'min:0', 'max:2048'],
        ]);

        $uploaded = $request->file('file');
        $storedPath = $uploaded->storeAs('knowledge_uploads', $uploaded->getClientOriginalName());
        $absolutePath = Storage::path($storedPath);

        $chunking = $request->string('chunking')->value() ?: 'auto';

        $chunkingStrategy = $chunking === 'static'
            ? KnowledgeUploadService::staticChunking(
                (int) ($request->input('max_chunk_size_tokens', 800)),
                (int) ($request->input('chunk_overlap_tokens', 400)),
            )
            : KnowledgeUploadService::autoChunking();

        try {
            $result = $service->uploadAndStore(
                absolutePath: $absolutePath,
                vectorStoreId: $request->input('vector_store_id'),
                vectorStoreName: $request->input('vector_store_name'),
                chunkingStrategy: $chunkingStrategy,
            );

            return response()->json([
                'success' => true,
                'message' => 'Knowledge uploaded successfully.',
                'data' => $result,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}
