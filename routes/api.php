<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ─── API Móvil CrediNica ──────────────────────────────────────────────────────
Route::prefix('mobile')->group(function () {

    // Público — no requiere token
    Route::post('/login', [MobileApiController::class, 'login']);

    // Protegido con Sanctum
    Route::middleware('auth:sanctum')->group(function () {
                Route::get('/cartera',   [MobileApiController::class, 'cartera']);
        Route::get('/dashboard', [MobileApiController::class, 'dashboard']);
        Route::post('/abono',    [MobileApiController::class, 'abono']);
        Route::post('/recibo',   [MobileApiController::class, 'recibo']);
        Route::get('/recibo/{id}', [MobileApiController::class, 'reciboPorId']);
    });
});

