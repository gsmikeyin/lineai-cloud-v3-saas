<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        return response()->json([
            'customers' => Customer::tenant($tenantId)->count(),
            'open_conversations' => Conversation::tenant($tenantId)->where('status', 'open')->count(),
            'messages_today' => Message::tenant($tenantId)->whereDate('created_at', today())->count(),
            'ai_messages_today' => Message::tenant($tenantId)->whereDate('created_at', today())->where('is_ai_generated', true)->count(),
        ]);
    }
}
