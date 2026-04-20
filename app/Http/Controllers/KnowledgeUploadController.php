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

    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;

        $items = $tenant->knowledgeSources()
            ->latest('id')
            ->paginate(20);

        return response()->json($items);
    }
}
