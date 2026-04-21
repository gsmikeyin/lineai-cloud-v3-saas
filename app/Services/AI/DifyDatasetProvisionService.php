<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DifyDatasetProvisionService
{
    public function createEmptyDataset(string $name, ?string $description = null): array
    {
        if (!config('services.dify.enabled')) {
            throw new RuntimeException('Dify is disabled.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.dify.dataset_api_key'),
            'Content-Type' => 'application/json',
        ])->post(
            rtrim(config('services.dify.base_url'), '/') . '/datasets',
            [
                'name' => $name,
                'description' => $description,
                'indexing_technique' => config('services.dify.indexing_technique', 'high_quality'),
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException('Create Dify dataset failed: ' . $response->body());
        }

        $json = $response->json();

        return [
            'id' => data_get($json, 'id'),
            'name' => data_get($json, 'name'),
            'raw' => $json,
        ];
    }
}
