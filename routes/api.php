<?php

use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthPasswordController;
use App\Http\Controllers\AuthVerificationController;
use App\Http\Controllers\ContactLeadController;
use App\Http\Controllers\DifyAppPoolController;
use App\Http\Controllers\DifyAppPoolUiController;
use App\Http\Controllers\DifyConversationController;
use App\Http\Controllers\DifyTestController;
use App\Http\Controllers\KnowledgeItemController;
use App\Http\Controllers\KnowledgeMatcherTestController;
use App\Http\Controllers\KnowledgeUploadController;
use App\Http\Controllers\LineBotSettingController;
use App\Http\Controllers\LineWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/line/webhook/{webhookKey}', [LineWebhookController::class, 'handle']);
Route::post('/forgot-password', [AuthPasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthPasswordController::class, 'resetPassword']);
Route::post('/verify-email', [AuthVerificationController::class, 'verify']);

Route::middleware('set.locale')->group(function () {
    Route::post('/contact', [ContactLeadController::class, 'store']);
});

Route::get('/login', function () {
    return response()->json([
        'message' => 'Please login first.',
    ], 401);
})->name('login');

Route::middleware('auth:sanctum')->put('/me/locale', [AuthController::class, 'updateLocale']);

Route::get('/email/verify/{id}/{hash}', [AuthVerificationController::class, 'verifySigned'])
    ->middleware('signed')
    ->name('verification.verify');

Route::middleware('auth:sanctum', 'role:super_admin,admin')->group(function () {
    Route::get('/dify-app-pools', [DifyAppPoolUiController::class, 'index']);
    Route::delete('/dify-app-pools/{difyAppPool}', [DifyAppPoolUiController::class, 'destroy']);
    Route::put('/dify-app-pools/{difyAppPool}', [DifyAppPoolController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'role:super_admin,admin,owner,sta'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);

    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/unread-summary', [ConversationController::class, 'unreadSummary']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/conversations/{conversation}/reply', [ConversationController::class, 'reply']);
    Route::post('/conversations/{conversation}/dify-reply', [DifyConversationController::class, 'reply']);
    Route::post('/conversations/{conversation}/handoff', [ConversationController::class, 'handoff']);
    Route::post('/conversations/{conversation}/resume-ai', [ConversationController::class, 'resumeAi']);

    Route::get('/knowledge', [KnowledgeController::class, 'index']);
    Route::post('/knowledge', [KnowledgeController::class, 'store']);
    Route::post('/knowledge/upload', [KnowledgeUploadController::class, 'upload']);
    Route::get('/knowledge/documents', [KnowledgeUploadController::class, 'index']);
    Route::delete('/knowledge/documents/{knowledgeSource}', [KnowledgeUploadController::class, 'destroy']);

    Route::get('/knowledge-items', [KnowledgeItemController::class, 'index']);
    Route::post('/knowledge-items', [KnowledgeItemController::class, 'store']);
    Route::get('/knowledge-items/{knowledgeItem}', [KnowledgeItemController::class, 'show']);
    Route::put('/knowledge-items/{knowledgeItem}', [KnowledgeItemController::class, 'update']);
    Route::delete('/knowledge-items/{knowledgeItem}', [KnowledgeItemController::class, 'destroy']);
    Route::post('/knowledge-items/test-match', [KnowledgeMatcherTestController::class, 'test']);

    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::post('/campaigns', [CampaignController::class, 'store']);

    Route::get('/settings/line-bot', [LineBotSettingController::class, 'show']);
    Route::put('/settings/line-bot', [LineBotSettingController::class, 'update']);

    Route::post('/email/verification-notification', [AuthVerificationController::class, 'send']);

    Route::get('/contact-leads', [ContactLeadController::class, 'index']);
    Route::get('/contact-leads/{contactLead}', [ContactLeadController::class, 'show']);
    Route::put('/contact-leads/{contactLead}', [ContactLeadController::class, 'update']);

    Route::get('/dify/test/last', [DifyTestController::class, 'last']);
    Route::post('/dify/test', [DifyTestController::class, 'test']);
});
