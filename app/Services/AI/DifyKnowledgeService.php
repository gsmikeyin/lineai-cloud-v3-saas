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

    $storedPath = $file->store("knowledge/{$tenant->id}", 'public');

    $record = KnowledgeSource::create([
        'tenant_id' => $tenant->id,
        'name' => $file->getClientOriginalName(),
        'file_path' => $storedPath,
        'mime_type' => $file->getClientMimeType(),
        'file_size' => $file->getSize(),
        'status' => 'uploaded',
        'source_type' => 'file',
        'dify_dataset_id' => $setting->dify_dataset_id,
    ]);

    $payload = [
        'indexing_technique' => config('services.dify.indexing_technique', 'high_quality'),
        'process_rule' => [
            'mode' => 'automatic',
        ],
    ];

    $httpResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . config('services.dify.dataset_api_key'),
    ])->attach(
        'file',
        fopen(Storage::disk('public')->path($storedPath), 'r'),
        $file->getClientOriginalName()
    )->post(
        rtrim(config('services.dify.base_url'), '/') . "/datasets/{$setting->dify_dataset_id}/document/create-by-file",
        [
            'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]
    );

    if ($httpResponse->failed()) {
        $record->update([
            'status' => 'failed',
            'error_message' => $httpResponse->body(),
            'meta' => [
                'status' => $httpResponse->status(),
                'body' => $httpResponse->json(),
            ],
        ]);

        throw new RuntimeException(
            'Create Dify document failed: ' . $httpResponse->body()
        );
    }

    $response = $httpResponse->json();

    Log::info('Dify upload response', [
        'tenant_id' => $tenant->id,
        'dataset_id' => $setting->dify_dataset_id,
        'response' => $response,
    ]);

    $record->update([
        'status' => 'indexing',
        'dify_document_id' => data_get($response, 'document.id') ?? data_get($response, 'document_id'),
        'dify_batch_id' => data_get($response, 'batch') ?? data_get($response, 'batch_id'),
        'indexing_status' => data_get($response, 'document.indexing_status') ?? 'waiting',
        'meta' => $response,
    ]);

    return $record->fresh();
}
}
