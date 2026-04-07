<?php


use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Controllers\LineWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OpenAIController;

use App\Http\Controllers\LineBotSettingController;

use App\Http\Controllers\KnowledgeItemController;
use App\Http\Controllers\KnowledgeMatcherTestController;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthPasswordController;
use App\Http\Controllers\AuthVerificationController;
use Illuminate\Http\Request;
use App\Http\Controllers\ContactLeadController;



Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);    
Route::post('/line/webhook/{webhookKey}', [LineWebhookController::class, 'handle']);


Route::post('/forgot-password', [AuthPasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthPasswordController::class, 'resetPassword']);




 
Route::middleware('set.locale')->group(function () {
    Route::post('/contact', [ContactLeadController::class, 'store']);
});


Route::get('/login', function () {
    return response()->json([
        'message' => 'Please login first.'
    ], 401);
})->name('login');


Route::middleware('auth:sanctum')->put('/me/locale', [AuthController::class, 'updateLocale']);


Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    return response()->json([
        'message' => 'Verification placeholder route.',
    ]);
})->middleware('signed')->name('verification.verify');


// 公開聯絡表單
Route::post('/contact', [ContactLeadController::class, 'store']);

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


    Route::get('/settings/line-bot', [LineBotSettingController::class, 'show']);
    Route::put('/settings/line-bot', [LineBotSettingController::class, 'update']);    
   
    Route::get('/knowledge-items', [KnowledgeItemController::class, 'index']);
    Route::post('/knowledge-items', [KnowledgeItemController::class, 'store']);
    Route::get('/knowledge-items/{knowledgeItem}', [KnowledgeItemController::class, 'show']);
    Route::put('/knowledge-items/{knowledgeItem}', [KnowledgeItemController::class, 'update']);
    Route::delete('/knowledge-items/{knowledgeItem}', [KnowledgeItemController::class, 'destroy']);
    Route::post('/knowledge-items/test-match', [KnowledgeMatcherTestController::class, 'test']);


    Route::post('/email/verification-notification', [AuthVerificationController::class, 'send']);
    Route::post('/verify-email', [AuthVerificationController::class, 'verify']);


    Route::get('/contact-leads', [ContactLeadController::class, 'index']);
    Route::get('/contact-leads/{contactLead}', [ContactLeadController::class, 'show']);
    Route::put('/contact-leads/{contactLead}', [ContactLeadController::class, 'update']);



});





Route::post('/upload-file', [OpenAIController::class, 'uploadFile']);
Route::post('/create-vector-store', [OpenAIController::class, 'VectorStore']);
Route::post('/add-file-to-vector-store', [OpenAIController::class, 'AddFileVectorStore']);
Route::post('/knowledge/upload', [KnowledgeController::class, 'upload']);


Route::get('/test', [OpenAIController::class, 'test']);