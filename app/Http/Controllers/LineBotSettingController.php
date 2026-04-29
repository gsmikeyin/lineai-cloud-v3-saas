<?php

namespace App\Http\Controllers;

use App\Models\TenantLineChannel;
use Illuminate\Http\Request;

class LineBotSettingController extends Controller
{
    public function show(Request $request)
    {
        $tenant = $request->user()->tenant;
        $tenant->load('lineChannel');
        $this->clearGeneratedDefaults($tenant);
        $tenant->load('lineChannel');

        return response()->json([
            'data' => $tenant->lineChannel,
            'webhook_url' => rtrim(config('app.url'), '/') . '/api/line/webhook/' . $tenant->webhook_key,
        ]);
    }


    
   public function update(Request $request)
   {
    $user = $request->user();

    if (!$user->hasVerifiedEmail()) {
        return response()->json([
            'message' => '請先完成 Email 驗證後再設定 LINE Bot'
        ], 403);
    }

    $tenant = $user->tenant;

    $validated = $request->validate([
        'channel_name' => ['nullable', 'string', 'max:255'],
        'channel_id' => ['nullable', 'string', 'max:255'],
        'channel_secret' => ['nullable', 'string'],
        'channel_access_token' => ['nullable', 'string'],
        'is_active' => ['required', 'boolean'],
    ]);

    $lineChannel = \App\Models\TenantLineChannel::updateOrCreate(
        ['tenant_id' => $tenant->id],
        array_merge($validated, [
            'provider' => 'line',
            'webhook_url' => rtrim(config('app.url'), '/') . '/api/line/webhook/' . $tenant->webhook_key,
        ])
    );

    return response()->json([
        'success' => true,
        'data' => $lineChannel,
        'webhook_url' => $lineChannel->webhook_url,
    ]);
   }

   protected function clearGeneratedDefaults($tenant): void
   {
    $lineChannel = $tenant->lineChannel;

    if (!$lineChannel) {
        return;
    }

    $updates = [];

    if ($lineChannel->channel_name === $tenant->name . ' Bot') {
        $updates['channel_name'] = null;
    }

    if (config('services.line_login.channel_id') && $lineChannel->channel_id === config('services.line_login.channel_id')) {
        $updates['channel_id'] = null;
    }

    if (config('services.line_login.channel_secret') && $lineChannel->channel_secret === config('services.line_login.channel_secret')) {
        $updates['channel_secret'] = null;
    }

    if ($updates) {
        $lineChannel->update($updates);
    }
   }


}
