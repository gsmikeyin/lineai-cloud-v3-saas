<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tenant_ai_settings')) {
            return;
        }

        Schema::create('tenant_ai_settings', function (Blueprint $table) {

        $table->id();
         $table->unsignedBigInteger('tenant_id')->unique();

    $table->string('provider')->default('dify');
    
    $table->string('dify_base_url')->default('https://api.dify.ai/v1');

    $table->text('dify_app_api_key')->nullable();
    $table->text('dify_dataset_api_key')->nullable();
    $table->string('dify_dataset_id')->nullable();
    $table->string('dify_app_mode')->default('chat'); // chat / workflow
    $table->boolean('is_active')->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
