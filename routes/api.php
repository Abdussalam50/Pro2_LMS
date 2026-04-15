<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatbotController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('ai')->group(function () {
    Route::post('/chat', [ChatbotController::class, 'sendMessage'])->name('api.ai.chat');
});

Route::prefix('rag')->group(function () {
    Route::post('/upload', [ChatbotController::class, 'uploadDocument'])->name('api.rag.upload');
    Route::post('/receive', [ChatbotController::class, 'receiveRagResponse'])->name('api.rag.receive');
});
