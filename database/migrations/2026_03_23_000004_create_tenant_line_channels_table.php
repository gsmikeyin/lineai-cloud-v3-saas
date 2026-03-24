<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_line_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            $table->string('provider')->default('line');
            $table->string('channel_name')->nullable();
            $table->string('channel_id')->nullable();
            $table->text('channel_secret')->nullable();
            $table->text('channel_access_token')->nullable();


            $table->string('basic_id')->nullable();
            $table->string('bot_user_id')->nullable();
            $table->string('webhook_url')->nullable();


            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_webhook_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            
            $table->unique(['tenant_id', 'provider']);


        });
    }
    public function down(): void { Schema::dropIfExists('tenant_line_channels'); }
};
