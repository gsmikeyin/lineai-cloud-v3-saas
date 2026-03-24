<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use Illuminate\Http\Request;

class KnowledgeItemController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $query = KnowledgeItem::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('keyword')) {
            $keyword = (string) $request->string('keyword');

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('question', 'like', "%{$keyword}%")
                    ->orWhere('answer', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'question' => ['nullable', 'string'],
            'answer' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'is_ai_enabled' => ['required', 'boolean'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string'],
        ]);

        $item = KnowledgeItem::create([
            'tenant_id' => $tenantId,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'question' => $validated['question'] ?? null,
            'answer' => $validated['answer'] ?? null,
            'content' => $validated['content'] ?? null,
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_ai_enabled' => $validated['is_ai_enabled'],
            'keywords' => $validated['keywords'] ?? [],
        ]);

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    public function show(Request $request, KnowledgeItem $knowledgeItem)
    {
        abort_if($knowledgeItem->tenant_id !== $request->user()->tenant_id, 403);

        return response()->json($knowledgeItem);
    }

    public function update(Request $request, KnowledgeItem $knowledgeItem)
    {
        abort_if($knowledgeItem->tenant_id !== $request->user()->tenant_id, 403);

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'question' => ['nullable', 'string'],
            'answer' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'is_ai_enabled' => ['required', 'boolean'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string'],
        ]);

        $knowledgeItem->update([
            'type' => $validated['type'],
            'title' => $validated['title'],
            'question' => $validated['question'] ?? null,
            'answer' => $validated['answer'] ?? null,
            'content' => $validated['content'] ?? null,
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_ai_enabled' => $validated['is_ai_enabled'],
            'keywords' => $validated['keywords'] ?? [],
        ]);

        return response()->json([
            'success' => true,
            'data' => $knowledgeItem->fresh(),
        ]);
    }

    public function destroy(Request $request, KnowledgeItem $knowledgeItem)
    {
        abort_if($knowledgeItem->tenant_id !== $request->user()->tenant_id, 403);

        $knowledgeItem->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}