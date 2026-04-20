<?php

namespace App\Http\Controllers;

use App\Services\Line\LineWebhookService;
use Illuminate\Http\Request;

class LineWebhookController extends Controller
{
    public function __construct(
        protected LineWebhookService $webhookService
    ) {}

    public function handle(Request $request, string $webhookKey)
    {
                    
         $this->webhookService->handle($request, $webhookKey);

        return response()->json([
            'success' => true,
        ]);


    }
}