<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('metric');
            $table->decimal('value', 12, 2)->default(0);
            $table->date('period_date');
            $table->string('period_type')->default('monthly');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'metric', 'period_date', 'period_type']);
        });
    }
    public function down(): void { Schema::dropIfExists('usage_logs'); }
};
