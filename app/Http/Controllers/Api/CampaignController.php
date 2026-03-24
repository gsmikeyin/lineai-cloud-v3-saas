<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Campaign::tenant($request->user()->tenant_id)->latest()->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message_text' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $campaign = Campaign::create([
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
            'name' => $data['name'],
            'type' => 'broadcast',
            'status' => empty($data['scheduled_at']) ? 'draft' : 'scheduled',
            'message_text' => $data['message_text'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'audience_type' => 'all',
        ]);

        return response()->json($campaign, 201);
    }
}
