<?php

namespace App\Services;

use App\Models\DifyApp;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Yaml\Yaml;
use RuntimeException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DifyAppDeployService
{
    protected string $baseUrl;
    protected string $email;
    protected string $password;
    protected array $cookies = [];
    protected ?string $csrfToken = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.dify.console_url'), '/');
        $this->email = config('services.dify.email');
        $this->password = config('services.dify.password');
    }

    // =========================
    // 🔥 主入口
    // =========================
    public function deployApp(
        string $inputDsl,
        string $outputDsl,
        array $datasetIds,
        ?string $description = null,
        string $keyName = 'auto-generated-key'
    ): array {
        
    
        $content = File::get($inputDsl);     
        $content = str_replace("$$@@__DATASETID__@@$$", $datasetIds[0], $content); // "Hello Laravel"

         

        // 2️⃣ 修改 description
        if ($description) {
            $content = str_replace("$$@@__CHAT BOT__@@$$", $description, $content); // "Hello Laravel"
        }

        // 4️⃣ 輸出 YAML
        File::put($outputDsl, $content);

        
        $dsl = $this->loadYaml($outputDsl);

        

        // Debug（強烈建議保留）
        // dump(Yaml::dump($dsl, 99, 2));

        // 5️⃣ 登入
        $this->login();

        // 6️⃣ import
        $result = $this->importDsl($content);

        if (($result['status'] ?? null) !== 'completed') {
            throw new RuntimeException('Import failed: ' . json_encode($result));
        }

        $appId = $result['app_id'];

        // 7️⃣ 建 API key
        $key = $this->createApiKey($appId, $keyName);
        //$apiKey = $key['api_key'] ?? null;

        $apiKey =
    $key['api_key']
    ?? $key['token']
    ?? ($key['data']['api_key'] ?? null)
    ?? ($key['data']['token'] ?? null);


        if (!$apiKey) {
            throw new RuntimeException('API key not found');
        }

        

        // 8️⃣ 存 DB
        $record = DifyApp::updateOrCreate(
            ['app_id' => $appId],
            [
                'app_mode' => $result['app_mode'] ?? null,
                'app_name' => $dsl['app']['name'] ?? null,
                'app_description' => $this->getDescription($dsl),
                'api_key' => $apiKey,
                'dsl_path' => $outputDsl,
            ]
        );

        return $record->toArray();
    }

    // =========================
    // 🔥 DSL 修復器（核心）
    // =========================
    protected function sanitizeDsl(array &$dsl): void
    {
        // 1. 全域 object → array
        $this->normalizeTypes($dsl);

        // 2. 修 workflow nodes
        $nodes = &$dsl['workflow']['graph']['nodes'] ?? [];

        foreach ($nodes as &$node) {
            if (($node['data']['type'] ?? null) !== 'knowledge-retrieval') {
                continue;
            }

            // 🔥 最關鍵：避免 {} 出現
            if (
                !isset($node['data']['query_attachment_selector']) ||
                is_object($node['data']['query_attachment_selector']) ||
                $node['data']['query_attachment_selector'] === []
            ) {
                $node['data']['query_attachment_selector'] = null;
            }

            // query_variable_selector 防炸
            if (
                isset($node['data']['query_variable_selector']) &&
                is_object($node['data']['query_variable_selector'])
            ) {
                $node['data']['query_variable_selector'] = null;
            }

            // dataset_ids 強制 array<string>
            $node['data']['dataset_ids'] = array_values(
                array_map('strval', $node['data']['dataset_ids'] ?? [])
            );
        }
    }

    // 🔧 object → array
    protected function normalizeTypes(&$data): void
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (is_array($data)) {
            foreach ($data as &$v) {
                $this->normalizeTypes($v);
            }
        }
    }

    // =========================
    // DSL 操作
    // =========================
    protected function replaceDatasetIds(array &$dsl, array $ids): void
    {
        $nodes = &$dsl['workflow']['graph']['nodes'] ?? [];

        foreach ($nodes as &$node) {
            if (($node['data']['type'] ?? null) === 'knowledge-retrieval') {
                $node['data']['dataset_ids'] = $ids;
            }
        }
    }

    protected function updateDescription(array &$dsl, string $desc): void
    {
        if (isset($dsl['app'])) {
            $dsl['app']['description'] = $desc;
        }
    }

    protected function getDescription(array $dsl): ?string
    {
        return $dsl['app']['description'] ?? null;
    }

    protected function loadYaml(string $path): array
    {
        return Yaml::parseFile($path);
    }

    // =========================
    // Dify API
    // =========================
    protected function login(): void
    {
        $res = Http::post("{$this->baseUrl}/console/api/login", [
            'email' => $this->email,
            'password' => base64_encode($this->password),
        ]);

        if ($res->failed()) {
            throw new RuntimeException($res->body());
        }

        foreach ($res->headers()['Set-Cookie'] ?? [] as $cookie) {
            [$k, $v] = explode('=', explode(';', $cookie)[0], 2);
            $this->cookies[$k] = $v;
        }

        $this->csrfToken = $this->cookies['csrf_token'] ?? null;
    }

    protected function importDsl(string $content): array
    {
        return $this->request('POST', '/console/api/apps/imports', [
            'mode' => 'yaml-content',
            'yaml_content' => $content,
        ]);
    }

    protected function createApiKey(string $appId, string $name): array
    {
        return $this->request('POST', "/console/api/apps/{$appId}/api-keys", [
            'name' => $name,
        ]);
    }

    protected function request(string $method, string $uri, array $payload = []): array
    {
        $cookie = collect($this->cookies)
            ->map(fn($v, $k) => "$k=$v")
            ->implode('; ');

        $res = Http::withHeaders([
            'X-CSRF-Token' => $this->csrfToken,
            'Cookie' => $cookie,
        ])->post("{$this->baseUrl}{$uri}", $payload);

        if ($res->failed()) {
            throw new RuntimeException($res->body());
        }

        return $res->json();
    }
}