<?php

namespace App\Services\AI;

use App\Models\KnowledgeSource;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DifyKnowledgeService
{
    public function uploadDocument(Tenant $tenant, UploadedFile $file): KnowledgeSource
    {
        $settings = $tenant->aiSetting;

        $storedPath = $file->store("knowledge/{$tenant->id}", 'public');

        $record = KnowledgeSource::create([
            'tenant_id' => $tenant->id,
            'name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'uploaded',
            'source_type' => 'file',
            'dify_dataset_id' => $settings->dify_dataset_id,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $settings->dify_dataset_api_key,
        ])->attach(
            'file',
            fopen(Storage::disk('public')->path($storedPath), 'r'),
            $file->getClientOriginalName()
        )->post(
            rtrim($settings->dify_base_url, '/') . "/datasets/{$settings->dify_dataset_id}/document/create-by-file",
            [
                'indexing_technique' => 'high_quality',
                'process_rule' => json_encode([
                    'mode' => 'automatic',
                ]),
            ]
        )->throw()->json();

        $documentId = data_get($response, 'document.id') ?? data_get($response, 'document_id');
        $batchId = data_get($response, 'batch') ?? data_get($response, 'batch_id');
        $indexingStatus = data_get($response, 'document.indexing_status') ?? 'waiting';

        $record->update([
            'status' => 'indexing',
            'dify_document_id' => $documentId,
            'dify_batch_id' => $batchId,
            'indexing_status' => $indexingStatus,
            'meta' => $response,
        ]);

        return $record->fresh();
    }


    public function syncDocumentStatus(Tenant $tenant, KnowledgeSource $source): KnowledgeSource
    {
        $settings = $tenant->aiSetting;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $settings->dify_dataset_api_key,
        ])->get(
            rtrim($settings->dify_base_url, '/') . "/datasets/{$settings->dify_dataset_id}/documents/{$source->dify_document_id}"
        )->throw()->json();

        $indexingStatus = data_get($response, 'indexing_status') ?? data_get($response, 'document.indexing_status');

        $source->update([
            'indexing_status' => $indexingStatus,
            'status' => $indexingStatus === 'completed' ? 'available' : 'indexing',
            'meta' => array_merge($source->meta ?? [], ['status_response' => $response]),
        ]);

        return $source->fresh();
    }

    
}
