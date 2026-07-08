<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\PullRequestController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
require __DIR__.'/auth.php';

// Protected routes (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    
    // Repositories
    Route::prefix('repositories')->name('repositories.')->group(function () {
        Route::get('/', [RepositoryController::class, 'index'])->name('index');
        Route::get('/connect', [RepositoryController::class, 'connect'])->name('connect');
        Route::post('/install', [RepositoryController::class, 'install'])->name('install');
        Route::get('/{repository}', [RepositoryController::class, 'show'])->name('show');
        Route::get('/{repository}/settings', [RepositoryController::class, 'settings'])->name('settings');
        Route::post('/{repository}/settings', [RepositoryController::class, 'updateSettings'])->name('settings.update');
        Route::delete('/{repository}', [RepositoryController::class, 'destroy'])->name('destroy');
    });
    
    // Pull Requests
    Route::prefix('pull-requests')->name('pull-requests.')->group(function () {
        Route::get('/', [PullRequestController::class, 'index'])->name('index');
        Route::get('/{pullRequest}', [PullRequestController::class, 'show'])->name('show');
        Route::post('/{pullRequest}/re-review', [PullRequestController::class, 'reReview'])->name('re-review');
    });
    
    // Reviews
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/{review}', [ReviewController::class, 'show'])->name('show');
        Route::post('/{review}/approve', [ReviewController::class, 'approve'])->name('approve');
        Route::post('/{review}/reject', [ReviewController::class, 'reject'])->name('reject');
    });
    
    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
    
    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/ai-provider', [SettingsController::class, 'updateAiProvider'])->name('ai-provider.update');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
    });
    
    // GitHub App
    Route::prefix('github-app')->name('github-app.')->group(function () {
        Route::get('/install', [App\Http\Controllers\GitHubAppController::class, 'install'])->name('install');
        Route::get('/callback', [App\Http\Controllers\GitHubAppController::class, 'callback'])->name('callback');
        Route::post('/sync', [App\Http\Controllers\GitHubAppController::class, 'sync'])->name('sync');
        Route::post('/{repository}/enable', [App\Http\Controllers\GitHubAppController::class, 'enable'])->name('enable');
        Route::post('/{repository}/disable', [App\Http\Controllers\GitHubAppController::class, 'disable'])->name('disable');
    });
});

// Webhook endpoint (public, uses GitHub signature validation)
Route::post('/github/webhook', [App\Http\Controllers\Webhook\GitHubWebhookController::class, 'handle'])
    ->name('github.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
