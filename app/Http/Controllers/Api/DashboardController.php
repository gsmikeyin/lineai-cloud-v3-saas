<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Message;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

         error_log("DashboardController stats  tenantId  = " . $tenantId);


        $conversationCount = Conversation::query()
            ->where('tenant_id', $tenantId)
            ->count();

        $unreadCount = Conversation::query()
            ->where('tenant_id', $tenantId)
            ->sum('unread_count');

        $customerCount = Customer::query()
            ->where('tenant_id', $tenantId)
            ->count();

        $aiReplyCount = Message::query()
            ->where('tenant_id', $tenantId)
            ->where('direction', 'outbound')
            ->where('sender_type', 'ai')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_count' => $conversationCount,
                'unread_count' => $unreadCount,
                'customer_count' => $customerCount,
                'ai_reply_count' => $aiReplyCount,
            ],
        ]);
    }
}