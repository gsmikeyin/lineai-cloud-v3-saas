<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dify_app_pools')) {
            Schema::table('dify_app_pools', function (Blueprint $table) {
                if (!Schema::hasColumn('dify_app_pools', 'meta')) {
                    $table->json('meta')->nullable();
                }
            });

            return;
        }

        Schema::create('dify_app_pools', function (Blueprint $table) {
            $table->id();
            $table->string('app_name');
            $table->text('app_api_key');
            $table->string('app_mode')->default('chat'); // chat / workflow
            $table->string('status')->default('available'); // available / assigned / disabled
            $table->unsignedBigInteger('assigned_tenant_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['assigned_tenant_id']);
        });
    }

    public function down(): void
    {
        //
    }
};
