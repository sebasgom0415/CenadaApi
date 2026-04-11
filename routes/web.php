<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\BoletinController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PublicoController;
use Illuminate\Support\Facades\Route;

// ── Pública ──────────────────────────────────────────────────────────────────
Route::get('/', [PublicoController::class, 'index'])->name('publico.index');
Route::get('/chart', [PublicoController::class, 'chart'])->name('publico.chart');

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Admin (requiere login) ────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Boletines
    Route::get('/boletines', [BoletinController::class, 'index'])->name('boletines.index');
    Route::get('/boletines/create', [BoletinController::class, 'create'])->name('boletines.create');
    Route::post('/boletines', [BoletinController::class, 'store'])->name('boletines.store');
    Route::get('/boletines/{boletin}', [BoletinController::class, 'show'])->name('boletines.show');
    Route::delete('/boletines/{boletin}', [BoletinController::class, 'destroy'])->name('boletines.destroy');

    // Productos
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');

    // API Token
    Route::get('/api-token', [ApiTokenController::class, 'index'])->name('api.token');
    Route::post('/api-token/generate', [ApiTokenController::class, 'generate'])->name('api.token.generate');
    Route::post('/api-token/revoke', [ApiTokenController::class, 'revoke'])->name('api.token.revoke');
});
