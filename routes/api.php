<?php

use App\Http\Controllers\Api\BoletinApiController;
use App\Http\Controllers\Api\ProductoApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SIMM CENADA — API REST
|--------------------------------------------------------------------------
| Todas las rutas requieren API token:
|   Header:    Authorization: Bearer {token}
|   O query:   ?api_token={token}
|
| Endpoints:
|   GET /api/boletines                              → lista de fechas
|   GET /api/boletines/latest                       → último boletín completo
|   GET /api/boletines/{fecha}                      → boletín por fecha (YYYY-MM-DD)
|   GET /api/boletines/{fecha}/producto/{nombre}    → producto en una fecha
|   GET /api/productos                              → catálogo de productos
|   GET /api/productos/{id}/historial               → historial de precios
*/

Route::middleware('api.key')->group(function () {
    // Boletines
    Route::get('/boletines',                              [BoletinApiController::class, 'index']);
    Route::get('/boletines/latest',                       [BoletinApiController::class, 'latest']);
    Route::get('/boletines/{fecha}',                      [BoletinApiController::class, 'show']);
    Route::get('/boletines/{fecha}/producto/{nombre}',    [BoletinApiController::class, 'producto']);

    // Productos
    Route::get('/productos',                              [ProductoApiController::class, 'index']);
    Route::get('/productos/{producto}/historial',         [ProductoApiController::class, 'historial']);
});
