<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

// Admin routes (require auth + admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    
    // AI Providers
    Route::get('/ai-providers', [AdminController::class, 'aiProviders'])->name('ai-providers');
    
    // Prompt Templates
    Route::get('/prompts', [AdminController::class, 'promptTemplates'])->name('prompts');
    
    // Queue
    Route::get('/queue', [AdminController::class, 'queue'])->name('queue');
    
    // Webhooks
    Route::get('/webhooks', [AdminController::class, 'webhooks'])->name('webhooks');
    
    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
