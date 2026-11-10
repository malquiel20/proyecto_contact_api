<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('health');

    Route::post('/auth/login', [AuthController::class, 'iniciarSesion'])
        ->middleware('throttle:login')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'cerrarSesion'])->name('auth.logout');

        Route::get('/profile', [ProfileController::class, 'mostrar'])->name('profile.show');
        Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'actualizar'])->name('profile.update');

        Route::apiResource('contacts', ContactController::class)->parameters(['contacts' => 'contacto']);
    });
});
