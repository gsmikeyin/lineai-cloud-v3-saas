<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_views')) {
            return;
        }

        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('route_name')->nullable()->index();
            $table->string('page_title')->nullable();
            $table->string('path')->index();
            $table->date('view_date')->index();
            $table->timestamp('viewed_at')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'view_date']);
            $table->index(['path', 'view_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
