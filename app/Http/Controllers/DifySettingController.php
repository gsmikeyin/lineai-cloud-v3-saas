<?php

namespace App\Http\Controllers;

use App\Models\TenantAiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DifySettingController extends Controller
{
    public function show(Request $request)
    {
        $setting = TenantAiSetting::firstOrCreate(
            ['tenant_id' => $request->user()->tenant_id],
            [
                'provider' => 'dify',
                'dify_base_url' => 'https://api.dify.ai/v1',
                'is_active' => false,
            ]
        );

        return response()->json([
            'data' => $setting,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'dify_base_url' => ['required', 'url'],
            'dify_app_api_key' => ['nullable', 'string'],
            'dify_dataset_api_key' => ['nullable', 'string'],
            'dify_dataset_id' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $setting = TenantAiSetting::updateOrCreate(
            ['tenant_id' => $request->user()->tenant_id],
            array_merge($validated, ['provider' => 'dify'])
        );

        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    public function test(Request $request)
    {
        $tenant = $request->user()->tenant;
        $settings = $tenant->aiSetting;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $settings->dify_app_api_key,
            'Content-Type' => 'application/json',
        ])->post(
            rtrim($settings->dify_base_url, '/') . '/chat-messages',
            [
                'inputs' => new \stdClass(),
                'query' => 'hello',
                'response_mode' => 'blocking',
                'user' => 'test-user',
            ]
        )->throw()->json();

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
}
