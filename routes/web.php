<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ruta de Dashboard para usuarios estándar
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// RUTAS DE PERFIL
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ----------------------------------------------------------------------
    // --- FLUJO DE CREACIÓN DE PEDIDOS (MÁXIMA PRIORIDAD PARA DIAGNÓSTICO) ---
    // ----------------------------------------------------------------------

    // 1. Índice de Creación: Muestra los 3 botones de categoría
    Route::get('orders/create-index', [OrderController::class, 'createIndex'])->name('orders.createIndex');

    // 2. Formulario de Creación: Muestra la tabla de productos filtrada por línea
    Route::get('orders/create/{lineNumber}', [OrderController::class, 'create'])->name('orders.create');

    Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

    // ----------------------------------------------------------------------


    // RUTAS DE PEDIDOS RESTANTES (CRUD)
    Route::resource('orders', OrderController::class)->only(['index', 'store', 'show', 'destroy']);

    // 3. Lógica de Actualización de Estado (Solo Admin/Production)
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('orders.updateStatus')
        ->middleware('role:admin|production');

    // 4. Rutas de Edición y Actualización (Manager, Admin, Production pueden editar pedidos Pendientes)
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])
        ->name('orders.edit')
        ->middleware('role:manager|admin|production');

    Route::put('orders/{order}', [OrderController::class, 'update'])
        ->name('orders.update')
        ->middleware('role:manager|admin|production');


    // RUTAS ADMINISTRATIVAS - Protegidas por el middleware 'role:admin|production'
    Route::middleware(['role:admin|production'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard Administrativo/Producción
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // CRUD de Categorías y Productos (Principalmente Admin)
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
    });

});

require __DIR__.'/auth.php';
