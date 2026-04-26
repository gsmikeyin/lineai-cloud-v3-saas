<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\CRM\ConversationReplyService;
use App\Services\CRM\HumanHandoffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    public function __construct(
        protected HumanHandoffService $humanHandoffService,
        protected ConversationReplyService $conversationReplyService,
    ) {}

    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $items = Conversation::query()
            ->with([
                'customer:id,tenant_id,display_name,avatar_url,email,phone,is_vip,last_interaction_at,total_messages,total_orders,total_spent',
                'assignedUser:id,name,email',
            ])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('unread_count')
            ->orderByDesc('human_handoff')
            ->orderByRaw('assigned_user_id IS NULL DESC')
            ->orderByDesc('last_message_at')
            ->paginate(30);

        return response()->json($items);
    }

    public function show(Request $request, Conversation $conversation)
    {
        abort_if($conversation->tenant_id !== $request->user()->tenant_id, 403);

        $conversation->update([
            'unread_count' => 0,
            'last_read_at' => now(),
        ]);

        $conversation->load([
            'customer',
            'assignedUser:id,name,email',
            'messages' => fn ($q) => $q->orderBy('id'),
        ]);

        return response()->json($conversation->fresh([
            'customer',
            'assignedUser',
            'messages',
        ]));
    }

    public function handoff(Request $request, Conversation $conversation)
    {
        abort_if($conversation->tenant_id !== $request->user()->tenant_id, 403);

        $conversation = $this->humanHandoffService->assign($conversation, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Conversation assigned to human support.',
            'conversation' => $conversation,
        ]);
    }

    public function resumeAi(Request $request, Conversation $conversation)
    {
        abort_if($conversation->tenant_id !== $request->user()->tenant_id, 403);

        $conversation = $this->humanHandoffService->disable($conversation);

        return response()->json([
            'success' => true,
            'message' => 'AI replies resumed.',
            'conversation' => $conversation,
        ]);
    }

    public function reply(Request $request, Conversation $conversation)
    {
        abort_if($conversation->tenant_id !== $request->user()->tenant_id, 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $message = $this->conversationReplyService->replyAsAgent(
                conversation: $conversation,
                user: $request->user(),
                text: $validated['message'],
            );
        } catch (\Throwable $e) {
            Log::error('Agent reply failed', [
                'conversation_id' => $conversation->id,
                'tenant_id' => $conversation->tenant_id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply sent.',
            'data' => $message,
        ]);
    }

    public function unreadSummary(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $count = Conversation::query()
            ->where('tenant_id', $tenantId)
            ->sum('unread_count');

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}
