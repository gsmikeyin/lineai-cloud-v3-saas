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
       Schema::create('knowledge_sources', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');

    $table->string('name');
    $table->string('file_path')->nullable();
    $table->string('mime_type')->nullable();
    $table->unsignedBigInteger('file_size')->nullable();

    $table->string('status')->default('uploaded'); // uploaded / indexing / available / failed
    $table->string('source_type')->default('file');

    $table->string('dify_dataset_id')->nullable();
    $table->string('dify_document_id')->nullable();
    $table->string('dify_batch_id')->nullable();
    $table->string('indexing_status')->nullable();
    $table->text('error_message')->nullable();

    $table->json('meta')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_sources');
    }
};
