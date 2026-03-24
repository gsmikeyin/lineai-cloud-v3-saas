<?php

namespace App\Http\Controllers;

use App\Services\Knowledge\KnowledgeMatcherService;
use Illuminate\Http\Request;

class KnowledgeMatcherTestController extends Controller
{
    public function __construct(
        protected KnowledgeMatcherService $knowledgeMatcherService
    ) {}

    public function test(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $tenant = $request->user()->tenant;

        $match = $this->knowledgeMatcherService->analyzeMatch(
            $tenant,
            $validated['message']
        );

        $knowledgeContext = $this->knowledgeMatcherService->getKnowledgeContext($tenant, 8);
        $promptRules = $this->knowledgeMatcherService->getPromptRules($tenant);

        return response()->json([
            'success' => true,
            'message' => $validated['message'],
            'match' => $match,
            'knowledge_context' => $knowledgeContext,
            'prompt_rules' => $promptRules,
        ]);
    }
}