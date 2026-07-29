<?php

use App\Http\Controllers\AIAssignmentController;
use App\Http\Controllers\PromptGeneratorController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/ai-evaluator');
});

// ==========================================
// AUTO-LOGIN REQUIRED ROUTES
// ==========================================

use App\Http\Controllers\AdminPromptController;

Route::middleware('auto-login')->group(function () {

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::get('/prompts', [AdminPromptController::class, 'index'])->name('admin.prompts.index');
        Route::post('/prompts/upload', [AdminPromptController::class, 'upload'])->name('admin.prompts.upload');
        Route::delete('/prompts/{prompt}', [\App\Http\Controllers\SavedPromptController::class, 'destroy'])->name('admin.prompts.destroy');
        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('admin.analytics');
    });

    // AI Assignment Evaluator
    Route::get('/ai-evaluator', [AIAssignmentController::class, 'index'])
        ->name('ai.evaluator');

    // Prompt Generator (Agent 1)
    Route::get('/prompt-generator', [PromptGeneratorController::class, 'index'])
        ->name('prompt.generator');
    Route::post('/prompt-generator/generate', [PromptGeneratorController::class, 'generate'])
        ->name('prompt.generator.generate');

    Route::post('/ai-evaluator/process', [AIAssignmentController::class, 'process'])
        ->name('ai.evaluator.process');

    Route::get('/ai-evaluator/{evaluation}', [AIAssignmentController::class, 'show'])
        ->name('ai.evaluator.show');

    Route::get('/ai-evaluator/{evaluation}/download/pdf', [AIAssignmentController::class, 'downloadPdf'])
        ->name('ai.evaluator.download.pdf');

    Route::get('/ai-evaluator/{evaluation}/download/docx', [AIAssignmentController::class, 'downloadDocx'])
        ->name('ai.evaluator.download.docx');

    Route::get('/ai-evaluator/{evaluation}/download/json', [AIAssignmentController::class, 'downloadJson'])
        ->name('ai.evaluator.download.json');
    // Prompts API
    Route::apiResource('prompts', \App\Http\Controllers\SavedPromptController::class)->except(['show']);

    // Evaluations API
    Route::get('/evaluations', [AIAssignmentController::class, 'list'])->name('evaluations.list');
    Route::delete('/evaluations/{evaluation}', [AIAssignmentController::class, 'destroy'])->name('evaluations.destroy');

});