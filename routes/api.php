<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Controllers\LineWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OpenAIController;




Route::post('/login', [AuthController::class, 'login']);




Route::post('/webhook/line', [LineWebhookController::class, 'handle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);

    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/conversations/{conversation}/reply', [ConversationController::class, 'reply']);

    Route::get('/knowledge', [KnowledgeController::class, 'index']);
    Route::post('/knowledge', [KnowledgeController::class, 'store']);

    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::post('/campaigns', [CampaignController::class, 'store']);


    Route::post('/conversations/{conversation}/handoff', [ConversationController::class, 'handoff']);
    Route::post('/conversations/{conversation}/resume-ai', [ConversationController::class, 'resumeAi']);    
    
    Route::middleware('auth:sanctum')->get('/conversations/unread-summary', [ConversationController::class, 'unreadSummary']);

});



Route::post('/upload-file', [OpenAIController::class, 'uploadFile']);
Route::post('/create-vector-store', [OpenAIController::class, 'VectorStore']);
Route::post('/add-file-to-vector-store', [OpenAIController::class, 'AddFileVectorStore']);
Route::post('/knowledge/upload', [KnowledgeController::class, 'upload']);