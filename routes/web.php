<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;

Route::redirect('/', '/login');

// Ruta de Dashboard para usuarios estándar
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// RUTAS PROTEGIDAS (Requieren autenticación)
Route::middleware('auth')->group(function () {

    // RUTAS DE PERFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ----------------------------------------------------------------------
    // --- FLUJO DE CREACIÓN DE PEDIDOS UNIFICADOS (MODIFICADO) ---
    // ----------------------------------------------------------------------

    // 1. Índice de Creación: Muestra las categorías para iniciar el pedido
    Route::get('orders/create-index', [OrderController::class, 'createIndex'])->name('orders.createIndex');

    // 2. Formulario de Creación: Muestra la tabla de productos filtrada por línea
    Route::get('orders/create/{categoryId}', [OrderController::class, 'create'])->name('orders.create');

    // 🚨 NUEVA RUTA: Guarda los productos seleccionados de UNA CATEGORÍA en la SESIÓN (la cesta)
    Route::post('/orders/add-item', [OrderController::class, 'addItem'])->name('orders.addItem');

    // 🚨 RUTA FINAL: Procesa el pedido UNIFICADO a partir de todos los ítems guardados en la sesión
    // Nota: El POST a '/orders' es ahora la finalización de la cesta.
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

    // ----------------------------------------------------------------------


    // RUTAS DE PEDIDOS RESTANTES (CRUD)
    // Usamos 'only' para que no cree rutas duplicadas para 'store', ya que la definimos arriba.
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);

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
        Route::patch('categories/{category}/togggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::resource('branches', BranchController::class);
        Route::patch('branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'create', 'store']);
    });

});

require __DIR__.'/auth.php';
