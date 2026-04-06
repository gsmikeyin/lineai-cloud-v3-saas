<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('company_name')->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('status')->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->string('timezone')->default('Asia/Taipei');
            $table->string('locale')->default('zh_TW');
            $table->string('currency', 10)->default('TWD');
            $table->text('brand_summary')->nullable();
            $table->text('ai_system_prompt')->nullable();
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('locale', 10)->default('zh_TW')->after('status');
        });
    }
    public function down(): void { Schema::dropIfExists('tenants'); }
};
