<?php

use App\Http\Controllers\Api\AforoApiController;
use App\Http\Controllers\Api\SocioApiController;
use Illuminate\Support\Facades\Route;

// API REST (prefijo /api). Respuestas en JSON.

// Aforo público: cuántas personas hay ahora y las franjas más concurridas.
Route::get('/aforo', [AforoApiController::class, 'show'])->middleware('throttle:60,1')->name('api.aforo');

// Portal del socio: token de Sanctum obtenido con DNI + PIN.
Route::prefix('socio')->name('api.socio.')->group(function () {
    Route::post('/login', [SocioApiController::class, 'login'])->middleware('throttle:10,1')->name('login');

    Route::middleware(['auth:sanctum', 'socio.token'])->group(function () {
        Route::get('/perfil', [SocioApiController::class, 'perfil'])->name('perfil');
        Route::get('/asistencias', [SocioApiController::class, 'asistencias'])->name('asistencias');
        Route::post('/logout', [SocioApiController::class, 'logout'])->name('logout');
    });
});
