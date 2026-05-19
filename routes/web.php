<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\StoryManagementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CodingPromptController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureListController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TableRepositoryController;
use App\Http\Controllers\TechnicalDesignController;
use App\Http\Controllers\AIAssignmentController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ==========================================
// LOGIN ROUTES
// ==========================================

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {

    $credentials = [
        'email' => $request->email,
        'password' => $request->password,
    ];

    if (Auth::attempt($credentials)) {

        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    return back()->with('error', 'Invalid email or password');

});

Route::post('/logout', function () {

    Auth::logout();

    return redirect('/login');

})->name('logout');

// ==========================================
// AUTH REQUIRED ROUTES
// ==========================================

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Stories
    Route::resource('stories', StoryController::class);

    Route::prefix('stories/{story}')->name('stories.')->group(function () {

        // Quiz
        Route::get('/quiz', [StoryController::class, 'quiz'])->name('quiz');
        Route::post('/quiz', [StoryController::class, 'saveQuiz'])->name('quiz.save');

        // AI Generation
        Route::post('/generate-story', [StoryController::class, 'generateStory'])->name('generate');
        Route::post('/upload-document', [StoryController::class, 'uploadDocument'])->name('upload');

        // Approval
        Route::post('/approve', [StoryController::class, 'approve'])->name('approve');

        // Versions
        Route::get('/versions', [StoryController::class, 'versions'])->name('versions');
        Route::delete('/versions/{version}', [StoryController::class, 'deleteVersion'])->name('versions.delete');

        // Feature Lists
        Route::get('/features', [FeatureListController::class, 'index'])->name('features.index');
        Route::post('/features/generate', [FeatureListController::class, 'generate'])->name('features.generate');
        Route::get('/features/{featureList}', [FeatureListController::class, 'show'])->name('features.show');
        Route::get('/features/{featureList}/export', [FeatureListController::class, 'export'])->name('features.export');

        // Technical Designs
        Route::get('/designs', [TechnicalDesignController::class, 'index'])->name('designs.index');
        Route::post('/designs/generate', [TechnicalDesignController::class, 'generate'])->name('designs.generate');
        Route::get('/designs/{design}', [TechnicalDesignController::class, 'show'])->name('designs.show');
        Route::put('/designs/{design}', [TechnicalDesignController::class, 'update'])->name('designs.update');

        // Coding Prompts
        Route::get('/prompts', [CodingPromptController::class, 'index'])->name('prompts.index');
        Route::post('/prompts/generate', [CodingPromptController::class, 'generate'])->name('prompts.generate');
        Route::get('/prompts/{prompt}', [CodingPromptController::class, 'show'])->name('prompts.show');
    });

    // Repository
    Route::get('/repository', [TableRepositoryController::class, 'index'])->name('repository.index');
    Route::get('/repository/{table}', [TableRepositoryController::class, 'show'])->name('repository.show');

    // AI Assignment Evaluator
    Route::get('/ai-evaluator', [AIAssignmentController::class, 'index'])
        ->name('ai.evaluator');

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

    // API
    Route::prefix('api')->name('api.')->group(function () {

        Route::get('/domains', [ApiController::class, 'domains'])->name('domains');

        Route::get('/business-units', [ApiController::class, 'businessUnits'])->name('business-units');
    });

    // ==========================================
    // ADMIN
    // ==========================================

    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::post('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');

        Route::get('/stories', [StoryManagementController::class, 'index'])->name('stories.index');

        Route::delete('/stories/{story}', [StoryManagementController::class, 'destroy'])->name('stories.destroy');

        Route::get('/config', [ConfigurationController::class, 'index'])->name('config.index');

        Route::put('/config', [ConfigurationController::class, 'update'])->name('config.update');

        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});