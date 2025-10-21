<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// RUTAS PÚBLICAS Y BÁSICAS
// =========================================================================

Route::get('/', function () {
    return view('welcome');
});

// Dashboard básico (requiere autenticación)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Rutas de Autenticación (login, registro, etc.)
require __DIR__.'/auth.php';

// =========================================================================
// GRUPO DE RUTAS PROTEGIDAS CON AUTENTICACIÓN
// =========================================================================

Route::middleware('auth')->group(function () {

    // RUTAS DE PERFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // =========================================================================
    // RUTAS ADMINISTRATIVAS (CATEGORÍAS Y PRODUCTOS)
    // Acceso: Admin y Producción
    // =========================================================================
    Route::middleware(['role:admin|production'])->prefix('admin')->name('admin.')->group(function () {

        // CRUD de Categorías
        Route::resource('categories', CategoryController::class);

        // CRUD de Productos
        Route::resource('products', ProductController::class);

        // Ruta de Dashboard Administrativo/Producción
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });

    // =========================================================================
    // RUTAS DE PEDIDOS (ORDERS)
    // =========================================================================

    // 1. Rutas del Recurso Orders que requieren 'manager|admin|production':
    // index, create, store, show
    Route::resource('orders', OrderController::class)->only([
        'index', 'create', 'store', 'show'
    ])->middleware('role:manager|admin|production');

    // 2. Rutas específicas para Gerentes (Editar y Actualizar)
    // Estas rutas son las que FALTABAN y causaban el error 'orders.edit'
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])
        ->name('orders.edit')
        ->middleware('role:manager');

    Route::put('orders/{order}', [OrderController::class, 'update'])
        ->name('orders.update')
        ->middleware('role:manager');

    // 3. Ruta específica para Producción/Admin (Actualizar solo el estado)
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('orders.updateStatus')
        ->middleware('role:admin|production');

});
