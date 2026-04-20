<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\AI\DifyChatService;
use Illuminate\Http\Request;

class DifyConversationController extends Controller
{
    public function __construct(
        protected DifyChatService $difyChatService
    ) {}

    public function reply(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->tenant_id === $request->user()->tenant_id, 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $result = $this->difyChatService->reply(
            tenant: $request->user()->tenant,
            conversation: $conversation,
            userId: (string) $conversation->customer_id,
            message: $validated['message']
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
