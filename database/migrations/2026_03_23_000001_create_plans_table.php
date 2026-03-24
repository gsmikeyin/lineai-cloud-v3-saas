<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->unsignedInteger('max_customers')->nullable();
            $table->unsignedInteger('max_team_members')->nullable();
            $table->unsignedInteger('max_monthly_ai_replies')->nullable();
            $table->unsignedInteger('max_monthly_broadcasts')->nullable();
            $table->unsignedInteger('max_automation_rules')->nullable();
            $table->boolean('supports_ai')->default(true);
            $table->boolean('supports_crm')->default(true);
            $table->boolean('supports_campaigns')->default(false);
            $table->boolean('supports_analytics')->default(false);
            $table->boolean('supports_team')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('plans'); }
};
