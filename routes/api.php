<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LabResults\LabResultCorrectionController;
use App\Http\Controllers\Api\V1\LabResults\LabResultEntryController;
use App\Http\Controllers\Api\V1\LabResults\LabResultPublishController;
use App\Http\Controllers\Api\V1\LabResults\PendingResultsController;
use App\Http\Controllers\Api\V1\LabResults\PublishedResultsController;
use Illuminate\Support\Facades\Route;

Route::middleware('tenant')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware(['tenant', 'jwt.auth'])->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['tenant', 'jwt.refresh'])->group(function (): void {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

// Módulo ASII-19 — Ingreso de resultados de laboratorio (orem6).
// Cada cliente depende de un único contrato ISP (ver docs/asii-19/week-02-isp).
Route::middleware(['tenant', 'jwt.auth'])->prefix('lab-results')->name('lab-results.')->group(function (): void {
    Route::middleware('role:TecnicoLab,api')->group(function (): void {
        Route::get('/pending', [PendingResultsController::class, 'index'])->name('pending');
        Route::post('/', [LabResultEntryController::class, 'store'])->name('store');
        Route::patch('/{result}/correct', [LabResultCorrectionController::class, 'update'])
            ->whereNumber('result')
            ->name('correct');
        Route::post('/{result}/publish', [LabResultPublishController::class, 'store'])
            ->whereNumber('result')
            ->name('publish');
    });

    Route::middleware('role:Médico|Admin,api')->group(function (): void {
        Route::get('/{result}', [PublishedResultsController::class, 'show'])
            ->whereNumber('result')
            ->name('show');
    });
});
