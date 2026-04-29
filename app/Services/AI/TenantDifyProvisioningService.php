<?php

namespace App\Services\AI;

use App\Models\DifyAppPool;
use App\Models\Tenant;
use App\Models\TenantAiSetting;
use App\Services\DifyAppDeployService;

class TenantDifyProvisioningService
{
    public function __construct(
        protected DifyDatasetProvisionService $difyDatasetProvisionService,
        protected DifyAppDeployService $difyAppDeployService
    ) {}

    public function provision(Tenant $tenant): TenantAiSetting
    {
        $datasetName = "{$tenant->name} KB ({$tenant->id})";

        $dataset = $this->difyDatasetProvisionService->createEmptyDataset(
            name: $datasetName,
            description: "Tenant {$tenant->id} knowledge base"
        );

        $difyApp = $this->difyAppDeployService->deployApp(
            inputDsl: storage_path('app/dify/template.yml'),
            outputDsl: storage_path("app/dify/output/tenant_{$tenant->id}.yml"),
            datasetIds: [$dataset['id']],
            name: 'CHATBOT_' . ($tenant->contact_name ?: $tenant->name) . '-' . ($tenant->contact_email ?: $tenant->id),
            description: "Tenant CHATBOT{$tenant->name}-{$tenant->id} AI App",
            keyName: "tenant-{$tenant->id}-key"
        );

        $pool = DifyAppPool::create([
            'app_name' => $difyApp['app_name'],
            'app_api_key' => $difyApp['api_key'],
            'app_mode' => 'chat',
            'status' => 'assigned',
            'assigned_tenant_id' => $tenant->id,
        ]);

        return TenantAiSetting::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'provider' => 'dify',
                'dify_dataset_id' => $dataset['id'],
                'dify_dataset_name' => $dataset['name'],
                'dify_app_api_key' => $pool->app_api_key,
                'dify_app_name' => $pool->app_name,
                'dify_app_mode' => $pool->app_mode,
                'is_active' => true,
                'dataset_bound' => false,
            ]
        );
    }
}
