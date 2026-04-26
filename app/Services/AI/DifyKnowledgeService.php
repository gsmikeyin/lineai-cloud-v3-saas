<?php

namespace App\Services\AI;

use App\Models\KnowledgeSource;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class DifyKnowledgeService
{
  public function uploadDocument(Tenant $tenant, UploadedFile $file): KnowledgeSource
{
    $setting = $tenant->aiSetting;

    if (!config('services.dify.enabled')) {
        throw new RuntimeException('Dify is disabled.');
    }

    if (!$setting || empty($setting->dify_dataset_id)) {
        throw new RuntimeException('Dify dataset not created for this tenant.');
    }

    $payload = [
        'indexing_technique' => config('services.dify.indexing_technique', 'high_quality'),
        'process_rule' => [
            'mode' => 'automatic',
        ],
    ];

    $storedPath = $file->store("knowledge/{$tenant->id}", 'public');
    $fileHandle = fopen(Storage::disk('public')->path($storedPath), 'r');

    try {
        $httpResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.dify.dataset_api_key'),
        ])->attach(
            'file',
            $fileHandle,
            $file->getClientOriginalName()
        )->post(
            rtrim(config('services.dify.base_url'), '/') . "/datasets/{$setting->dify_dataset_id}/document/create-by-file",
            [
                'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]
        );

        if ($httpResponse->failed()) {
            throw new RuntimeException(
                'Create Dify document failed: ' . $httpResponse->body()
            );
        }
    } catch (\Throwable $e) {
        Storage::disk('public')->delete($storedPath);
        throw $e;
    } finally {
        if (is_resource($fileHandle)) {
            fclose($fileHandle);
        }
    }

    $response = $httpResponse->json();

    Log::info('Dify upload response', [
        'tenant_id' => $tenant->id,
        'dataset_id' => $setting->dify_dataset_id,
        'response' => $response,
    ]);

    return KnowledgeSource::create([
        'tenant_id' => $tenant->id,
        'name' => $file->getClientOriginalName(),
        'file_path' => $storedPath,
        'mime_type' => $file->getClientMimeType(),
        'file_size' => $file->getSize(),
        'status' => 'indexing',
        'source_type' => 'file',
        'dify_dataset_id' => $setting->dify_dataset_id,
        'dify_document_id' => data_get($response, 'document.id') ?? data_get($response, 'document_id'),
        'dify_batch_id' => data_get($response, 'batch') ?? data_get($response, 'batch_id'),
        'indexing_status' => data_get($response, 'document.indexing_status') ?? 'waiting',
        'meta' => $response,
    ]);
}

public function deleteDocument(KnowledgeSource $source): void
{
    if (
        config('services.dify.enabled') &&
        $source->dify_dataset_id &&
        $source->dify_document_id &&
        config('services.dify.dataset_api_key')
    ) {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.dify.dataset_api_key'),
        ])->delete(
            rtrim(config('services.dify.base_url'), '/') . "/datasets/{$source->dify_dataset_id}/documents/{$source->dify_document_id}"
        );

        if ($response->failed()) {
            Log::warning('Delete Dify document failed', [
                'knowledge_source_id' => $source->id,
                'tenant_id' => $source->tenant_id,
                'dataset_id' => $source->dify_dataset_id,
                'document_id' => $source->dify_document_id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    if ($source->file_path) {
        Storage::disk('public')->delete($source->file_path);
    }

    $source->delete();
}
}
