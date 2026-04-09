<?php

use Hristijans\AiStager\Http\Controllers\StagerDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('ai-stager.dashboard.middleware', ['web', 'auth']))
    ->prefix(config('ai-stager.dashboard.path', 'stager'))
    ->name('ai-stager.')
    ->group(function () {
        Route::get('/', [StagerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/logs', [StagerDashboardController::class, 'logs'])->name('logs');
    });
