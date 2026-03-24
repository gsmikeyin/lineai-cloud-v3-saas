<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\CRM\ConversationReplyService;
use App\Services\CRM\HumanHandoffService;
use Illuminate\Http\Request;

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


        $this->humanHandoffService->assign($conversation, $request->user());

        return response()->json([
            'success' => true,
            'message' => '已由人工客服接手',
            'conversation' => $conversation->fresh(),
        ]);
    }

    public function resumeAi(Request $request, Conversation $conversation)
    {
        abort_if($conversation->tenant_id !== $request->user()->tenant_id, 403);


        $this->humanHandoffService->disable($conversation);

        return response()->json([
            'success' => true,
            'message' => '已切回 AI 自動回覆',
            'conversation' => $conversation->fresh(),
        ]);
    }

    public function reply(Request $request, Conversation $conversation)
    {

    abort_if($conversation->tenant_id !== $request->user()->tenant_id, 403);

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $message = $this->conversationReplyService->replyAsAgent(
            conversation: $conversation,
            user: $request->user(),
            text: $validated['message'],
        );

        return response()->json([
            'success' => true,
            'message' => '已送出',
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