<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('webhook_key', 64)->nullable()->unique()->after('slug');
        });

        DB::table('tenants')->orderBy('id')->chunkById(100, function ($tenants) {
            foreach ($tenants as $tenant) {
                if (empty($tenant->webhook_key)) {
                    DB::table('tenants')
                        ->where('id', $tenant->id)
                        ->update([
                            'webhook_key' => Str::random(32),
                        ]);
                }
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('webhook_key', 64)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropUnique(['webhook_key']);
            $table->dropColumn('webhook_key');
        });
    }
};