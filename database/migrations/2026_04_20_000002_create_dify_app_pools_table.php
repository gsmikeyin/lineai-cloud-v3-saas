<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dify_app_pools', function (Blueprint $table) {
            $table->id();
            $table->string('app_name');
            $table->text('app_api_key');
            $table->string('app_mode')->default('chat');
            $table->string('status')->default('available');
            $table->unsignedBigInteger('assigned_tenant_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dify_app_pools');
    }
};
