<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // RUTAS ADMINISTRATIVAS - Protegidas por el middleware 'role:admin|production'
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

    // Rutas de Recurso para Órdenes
    Route::middleware(['auth', 'role:manager|admin|production'])->group(function () {
        // Definimos solo los métodos que usaremos en el CRUD de Pedidos
        Route::resource('orders', OrderController::class)->only([
            'index', 'create', 'store', 'show'
        ]);
});

});

require __DIR__.'/auth.php';
