<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dify_apps', function (Blueprint $table) {
            $table->id();
            $table->string('app_id')->unique();
            $table->string('app_mode')->nullable();
            $table->string('app_name')->nullable();
            $table->text('app_description')->nullable();
            $table->text('api_key')->nullable();
            $table->string('dsl_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dify_apps');
    }
};