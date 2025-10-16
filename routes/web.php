<?php

use App\Http\Controllers\CategoryController; // Importar
use App\Http\Controllers\ProductController;   // Importar
use App\Http\Controllers\ProfileController;
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
            return view('admin.dashboard'); // Crearemos esta vista más adelante
        })->name('dashboard');
    });
});

require __DIR__.'/auth.php';
