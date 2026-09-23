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
                Route::get('/cartera',            [MobileApiController::class, 'cartera']);
        Route::get('/dashboard',          [MobileApiController::class, 'dashboard']);
        Route::post('/abono',             [MobileApiController::class, 'abono']);
        Route::post('/recibo',            [MobileApiController::class, 'recibo']);
        Route::get('/recibo/{id}',        [MobileApiController::class, 'reciboPorId']);
        Route::get('/clientes-externos',  [MobileApiController::class, 'clientesExternos']);
        Route::get('/mis-clientes',       [MobileApiController::class, 'misClientes']);
        Route::get('/mobile_clients',     [MobileApiController::class, 'misClientes']);
        Route::get('/cliente-detalle',    [MobileApiController::class, 'clienteDetalle']);
        Route::get('/mobile_client_detail', [MobileApiController::class, 'clienteDetalle']);
        Route::post('/mobile_create_credit', [MobileApiController::class, 'crearSolicitud']);
        Route::post('/mobile_create_client', [MobileApiController::class, 'crearCliente']);
        Route::get('/departamentos-municipios', [MobileApiController::class, 'getDepartamentoMunicipios']);
        Route::get('/requests',           [MobileApiController::class, 'requests']);
        Route::post('/approve-credit',    [MobileApiController::class, 'approveCredit']);
        Route::post('/reject-credit',     [MobileApiController::class, 'rejectCredit']);
        Route::get('/disbursements',      [MobileApiController::class, 'disbursements']);
        Route::post('/disburse-credit',   [MobileApiController::class, 'disburseCredit']);
        Route::post('/deny-disbursement', [MobileApiController::class, 'denyDisbursement']);
    });
});

