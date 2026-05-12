<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auth_events')) {
            return;
        }

        Schema::create('auth_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 30)->index();
            $table->string('provider', 30)->default('email');
            $table->date('event_date')->index();
            $table->timestamp('occurred_at')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['event_type', 'event_date']);
            $table->index(['tenant_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_events');
    }
};
