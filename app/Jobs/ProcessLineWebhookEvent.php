<?php

namespace App\Jobs;

use App\Services\Line\LineWebhookEventProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLineWebhookEvent implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $tenantId,
        public array $event
    ) {}

    public function handle(LineWebhookEventProcessor $processor): void
    {
        $processor->process($this->tenantId, $this->event);
    }
}