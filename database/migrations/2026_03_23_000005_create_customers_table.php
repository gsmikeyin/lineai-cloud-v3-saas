<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('source')->default('line');
            $table->string('line_user_id')->nullable();
            $table->string('display_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('language', 20)->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_vip')->default(false);
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('gender', 30)->nullable();
            $table->date('birthday')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->unsignedInteger('total_messages')->default(0);
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->timestamp('first_interaction_at')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->json('attributes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'line_user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
