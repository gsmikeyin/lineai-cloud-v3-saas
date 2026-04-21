<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_ai_settings')) {
            Schema::create('tenant_ai_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->unique();
                $table->string('provider')->default('dify');
                $table->string('dify_dataset_id')->nullable();
                $table->string('dify_dataset_name')->nullable();
                $table->text('dify_app_api_key')->nullable();
                $table->string('dify_app_name')->nullable();
                $table->string('dify_app_mode')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('dataset_bound')->default(false);
                $table->timestamp('dataset_bound_at')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table('tenant_ai_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_ai_settings', 'dify_dataset_name')) {
                $table->string('dify_dataset_name')->nullable()->after('dify_dataset_id');
            }
            if (!Schema::hasColumn('tenant_ai_settings', 'dify_app_api_key')) {
                $table->text('dify_app_api_key')->nullable()->after('dify_dataset_name');
            }
            if (!Schema::hasColumn('tenant_ai_settings', 'dify_app_name')) {
                $table->string('dify_app_name')->nullable()->after('dify_app_api_key');
            }
            if (!Schema::hasColumn('tenant_ai_settings', 'dify_app_mode')) {
                $table->string('dify_app_mode')->nullable()->after('dify_app_name');
            }
            if (!Schema::hasColumn('tenant_ai_settings', 'dataset_bound')) {
                $table->boolean('dataset_bound')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('tenant_ai_settings', 'dataset_bound_at')) {
                $table->timestamp('dataset_bound_at')->nullable()->after('dataset_bound');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
