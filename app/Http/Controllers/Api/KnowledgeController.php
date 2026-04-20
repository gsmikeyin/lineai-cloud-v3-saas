<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use App\Services\AI\DifyKnowledgeService;


class KnowledgeController extends Controller
{
      public function __construct(
        protected DifyKnowledgeService $difyKnowledgeService
    ) {}


    
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


   public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,txt,doc,docx,md'],
        ]);

        $tenant = $request->user()->tenant;

        $record = $this->difyKnowledgeService->uploadDocument(
            tenant: $tenant,
            file: $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data' => $record,
        ], 201);
    }


}
