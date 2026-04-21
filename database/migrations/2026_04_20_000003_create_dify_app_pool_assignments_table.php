<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dify_app_pool_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dify_app_pool_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('action');
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dify_app_pool_assignments');
    }
};
