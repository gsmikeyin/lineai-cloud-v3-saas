<?php

namespace App\Http\Controllers;

use App\Services\Line\LineWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LineWebhookController extends Controller
{
    public function __construct(
        protected LineWebhookService $webhookService
    ) {}

    public function handle(Request $request): JsonResponse
    {
       $this->webhookService->handle($request);

        return response()->json([
            'success' => true,
        ], Response::HTTP_OK);
    }
}
