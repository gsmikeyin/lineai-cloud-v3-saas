<?php

namespace App\Http\Controllers;

use App\Models\DifyAppPool;
use App\Models\DifyAppPoolAssignment;
use App\Models\TenantAiSetting;
use Illuminate\Support\Facades\DB;

class DifyAppPoolUiController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => DifyAppPool::query()
                ->with('assignedTenant:id,name,contact_name,contact_email')
                ->latest('id')
                ->get(),
        ]);
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
