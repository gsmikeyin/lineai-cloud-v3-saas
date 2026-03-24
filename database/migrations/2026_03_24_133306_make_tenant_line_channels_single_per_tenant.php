<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 先清理同 tenant 多筆資料的情況：保留 id 最小的一筆
        $duplicates = DB::table('tenant_line_channels')
            ->select('tenant_id')
            ->groupBy('tenant_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('tenant_id');

        foreach ($duplicates as $tenantId) {
            $keepId = DB::table('tenant_line_channels')
                ->where('tenant_id', $tenantId)
                ->orderBy('id')
                ->value('id');

            DB::table('tenant_line_channels')
                ->where('tenant_id', $tenantId)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('tenant_line_channels', function (Blueprint $table) {
            $table->unique('tenant_id', 'tenant_line_channels_tenant_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_line_channels', function (Blueprint $table) {
            $table->dropUnique('tenant_line_channels_tenant_id_unique');
        });
    }
};