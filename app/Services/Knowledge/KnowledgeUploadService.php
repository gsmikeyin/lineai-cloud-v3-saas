<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use App\Services\AI\DifyKnowledgeService;
use Illuminate\Http\UploadedFile;
use App\Models\Tenant;


class KnowledgeUploadService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(
       protected DifyKnowledgeService $difyKnowledgeService   
    )
    {      
        $this->baseUrl = rtrim(config('services.openai.base_url'), '/');
        $this->apiKey = (string) config('services.openai.key');
        if (empty($this->apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }        
    }

     public function upload(Tenant $tenant, UploadedFile $file)
    {
        return $this->difyKnowledgeService->uploadDocument($tenant, $file);
    }



    protected function client(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(120);
    }

    protected function ensureSuccess(Response $response, string $action): array
    {
        if ($response->failed()) {
            throw new RuntimeException(
                "{$action} failed: HTTP {$response->status()} - " . $response->body()
            );
        }

        return $response->json();
    }

    /**
     * Step 1: Upload a local file to OpenAI Files API.
     */
    public function uploadFile(string $absolutePath, ?string $filename = null, string $purpose = 'assistants'): array
    {
        if (!is_file($absolutePath)) {
            throw new RuntimeException("File not found: {$absolutePath}");
        }

        $filename ??= basename($absolutePath);

        $response = $this->client()
            ->attach('file', fopen($absolutePath, 'r'), $filename)
            ->post("{$this->baseUrl}/files", [
                'purpose' => $purpose,
            ]);

        return $this->ensureSuccess($response, 'Upload file');
    }

    /**
     * Step 2A: Create a vector store and optionally attach uploaded files.
     */
    public function createVectorStore(
        string $name,
        array $fileIds = [],
        ?array $chunkingStrategy = null,
        array $metadata = []
    ): array {
        $payload = [
            'name' => $name,
        ];

        if (!empty($fileIds)) {
            $payload['file_ids'] = array_values($fileIds);
        }

        if (!empty($chunkingStrategy)) {
            $payload['chunking_strategy'] = $chunkingStrategy;
        }

        if (!empty($metadata)) {
            $payload['metadata'] = $metadata;
        }

        $response = $this->client()->post("{$this->baseUrl}/vector_stores", $payload);

        return $this->ensureSuccess($response, 'Create vector store');
    }

    /**
     * Step 2B: Attach one uploaded file to an existing vector store.
     */
    public function attachFileToVectorStore(
        string $vectorStoreId,
        string $fileId,
        ?array $chunkingStrategy = null,
        array $attributes = []
    ): array {
        $payload = [
            'file_id' => $fileId,
        ];

        if (!empty($chunkingStrategy)) {
            $payload['chunking_strategy'] = $chunkingStrategy;
        }

        if (!empty($attributes)) {
            $payload['attributes'] = $attributes;
        }

        $response = $this->client()->post(
            "{$this->baseUrl}/vector_stores/{$vectorStoreId}/files",
            $payload
        );

        return $this->ensureSuccess($response, 'Attach file to vector store');
    }

    /**
     * Step 2C: Batch attach many uploaded files to an existing vector store.
     */
    public function attachFilesBatchToVectorStore(string $vectorStoreId, array $fileIds): array
    {
        $payload = [
            'file_ids' => array_values($fileIds),
        ];

        $response = $this->client()->post(
            "{$this->baseUrl}/vector_stores/{$vectorStoreId}/file_batches",
            $payload
        );

        return $this->ensureSuccess($response, 'Attach files batch to vector store');
    }

    /**
     * One-stop helper:
     * Upload a local file, then either create a new vector store or attach to an existing one.
     */
    public function uploadAndStore(
        string $absolutePath,
        ?string $vectorStoreId = null,
        ?string $vectorStoreName = null,
        ?array $chunkingStrategy = null
    ): array {
        $file = $this->uploadFile($absolutePath);
        $fileId = $file['id'] ?? null;

        if (!$fileId) {
            throw new RuntimeException('Upload succeeded but no file id returned.');
        }

        if ($vectorStoreId) {
            $vectorStoreFile = $this->attachFileToVectorStore(
                $vectorStoreId,
                $fileId,
                $chunkingStrategy
            );

            return [
                'mode' => 'attached_to_existing_store',
                'file' => $file,
                'vector_store_file' => $vectorStoreFile,
            ];
        }

        $vectorStoreName ??= 'knowledge-store-' . now()->format('Ymd-His');

        $vectorStore = $this->createVectorStore(
            $vectorStoreName,
            [$fileId],
            $chunkingStrategy
        );

        return [
            'mode' => 'created_new_store',
            'file' => $file,
            'vector_store' => $vectorStore,
        ];
    }

    /**
     * Recommended chunking strategies
     */
    public static function autoChunking(): array
    {
        return [
            'type' => 'auto',
        ];
    }

    public static function staticChunking(int $maxChunkSizeTokens = 800, int $chunkOverlapTokens = 400): array
    {
        return [
            'type' => 'static',
            'static' => [
                'max_chunk_size_tokens' => $maxChunkSizeTokens,
                'chunk_overlap_tokens' => $chunkOverlapTokens,
            ],
        ];
    }
}