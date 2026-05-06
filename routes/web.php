<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\Admin\ApiUsuariosController;
use App\Http\Controllers\Admin\ApiLogsController;
use App\Http\Controllers\BoletinController;
use App\Http\Controllers\MiCuentaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PublicoController;
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;

// ── Pública ──────────────────────────────────────────────────────────────────
Route::get('/', [PublicoController::class, 'index'])->name('publico.index');
Route::get('/chart', [PublicoController::class, 'chart'])->name('publico.chart');
Route::get('/productos', [PublicoController::class, 'productos'])->name('publico.productos');
Route::get('/productos/{producto}', [PublicoController::class, 'productoShow'])->name('publico.productos.show');

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Registro público de API ───────────────────────────────────────────────────
Route::get('/registro', [RegistroController::class, 'show'])->name('registro');
Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');

// ── Mi cuenta (usuarios API autenticados) ─────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/mi-cuenta', [MiCuentaController::class, 'index'])->name('mi-cuenta.index');
    Route::post('/mi-cuenta/regenerar', [MiCuentaController::class, 'regenerarToken'])->name('mi-cuenta.regenerar');
});

// ── Admin (requiere login + rol admin) ────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Boletines
    Route::get('/boletines', [BoletinController::class, 'index'])->name('boletines.index');
    Route::get('/boletines/create', [BoletinController::class, 'create'])->name('boletines.create');
    Route::post('/boletines', [BoletinController::class, 'store'])->name('boletines.store');
    Route::post('/boletines/fetch-email', [BoletinController::class, 'fetchFromEmail'])->name('boletines.fetch-email');
    Route::get('/boletines/{boletin}', [BoletinController::class, 'show'])->name('boletines.show');
    Route::delete('/boletines/{boletin}', [BoletinController::class, 'destroy'])->name('boletines.destroy');

    // Productos
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');

    // API Token (propio del admin)
    Route::get('/api-token', [ApiTokenController::class, 'index'])->name('api.token');
    Route::post('/api-token/generate', [ApiTokenController::class, 'generate'])->name('api.token.generate');
    Route::post('/api-token/revoke', [ApiTokenController::class, 'revoke'])->name('api.token.revoke');

    // Gestión de usuarios API
    Route::get('/api-usuarios', [ApiUsuariosController::class, 'index'])->name('api-usuarios.index');
    Route::get('/api-usuarios/{user}', [ApiUsuariosController::class, 'show'])->name('api-usuarios.show');
    Route::post('/api-usuarios/{user}/toggle', [ApiUsuariosController::class, 'toggleActivo'])->name('api-usuarios.toggle');
    Route::delete('/api-usuarios/{user}', [ApiUsuariosController::class, 'destroy'])->name('api-usuarios.destroy');

    // Logs de consultas
    Route::get('/api-logs', [ApiLogsController::class, 'index'])->name('api-logs.index');
});
