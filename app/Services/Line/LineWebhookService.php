<?php

namespace App\Services\Line;

use App\Jobs\ProcessLineWebhookEvent;
use App\Services\Tenant\TenantResolverService;
use Illuminate\Http\Request;

class LineWebhookService
{
    public function __construct(
        protected LineSignatureService $signatureService,
        protected TenantResolverService $tenantResolverService,
    ) {}

    public function handle(Request $request, string $webhookKey): void
    {
        $tenant = $this->tenantResolverService->resolveByWebhookKey($webhookKey);

        abort_unless($tenant->lineChannel, 404, 'LINE bot config not found.');

        $this->signatureService->verify(
            $request,
            $tenant->lineChannel->channel_secret
        );

        $payload = $request->json()->all();
        $events = $payload['events'] ?? [];

        foreach ($events as $event) {
            ProcessLineWebhookEvent::dispatch($tenant->id, $event);
        }
    }
}