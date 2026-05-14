<?php

namespace App\Http\Controllers;

use App\Models\DifyAppPool;
use App\Models\DifyAppPoolAssignment;
use App\Models\TenantAiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DifyAppPoolUiController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $keyword = trim((string) ($validated['keyword'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = DifyAppPool::query()
            ->with('assignedTenant:id,name,contact_name,contact_email')
            ->latest('id');

        if ($keyword !== '') {
            $query->whereHas('assignedTenant', function ($tenantQuery) use ($keyword) {
                $tenantQuery->where('contact_name', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('contact_email', 'like', "%{$keyword}%");
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function destroy(DifyAppPool $difyAppPool)
    {
        DB::transaction(function () use ($difyAppPool) {
            $tenantId = $difyAppPool->assigned_tenant_id;

            if ($tenantId) {
                DifyAppPoolAssignment::create([
                    'dify_app_pool_id' => $difyAppPool->id,
                    'tenant_id' => $tenantId,
                    'action' => 'delete',
                    'remark' => 'Deleted from Dify App Pool manager.',
                ]);

                TenantAiSetting::where('tenant_id', $tenantId)->delete();
            }

            $difyAppPool->delete();
        });

        return response()->json([
            'success' => true,
        ]);
    }
}
