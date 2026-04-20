<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DifyDatasetProvisionService
{
    public function createEmptyDataset(
        string $baseUrl,
        string $datasetApiKey,
        string $name,
        ?string $description = null,
        string $indexingTechnique = 'high_quality'
    ): array {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $datasetApiKey,
            'Content-Type' => 'application/json',
        ])->post(
            rtrim($baseUrl, '/') . '/datasets',
            [
                'name' => $name,
                'description' => $description,
                'indexing_technique' => $indexingTechnique,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Create Dify dataset failed: ' . $response->body()
            );
        }

        $json = $response->json();

        return [
            'id' => data_get($json, 'id'),
            'name' => data_get($json, 'name'),
            'raw' => $json,
        ];
    }
}