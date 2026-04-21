<?php

namespace App\Http\Controllers;

use App\Models\DifyAppPool;
use Illuminate\Http\Request;

class DifyAppPoolController extends Controller
{
    public function index(Request $request)
    {
        $items = DifyAppPool::query()->latest('id')->paginate(20);
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_api_key' => ['required', 'string'],
            'app_mode' => ['required', 'in:chat,workflow'],
        ]);

        $item = DifyAppPool::create([
            ...$validated,
            'status' => DifyAppPool::STATUS_AVAILABLE,
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, DifyAppPool $difyAppPool)
    {
        $validated = $request->validate([
            'app_name' => ['sometimes', 'string', 'max:255'],
            'app_api_key' => ['sometimes', 'string'],
            'app_mode' => ['sometimes', 'in:chat,workflow'],
            'status' => ['sometimes', 'in:available,assigned,disabled'],
        ]);

        $difyAppPool->update($validated);

        return response()->json([
            'success' => true,
            'data' => $difyAppPool->fresh(),
        ]);
    }
}
