<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CorrelativeService;

class OrderController extends Controller
{
    // =======================================================
    // --- FLUJO DE CREACIÓN DE PEDIDOS UNIFICADOS (Cesta) ---
    // =======================================================

    /**
     * Muestra la lista de categorías para empezar un nuevo pedido.
     */
    public function createIndex(InventoryService $inventoryService)
    {
        if (!Auth::check() || Auth::user()->role !== 'manager') {
            return redirect()->route('orders.index')->with('error', 'Solo los gerentes pueden iniciar nuevos pedidos.');
        }

        $user = Auth::user();
        $categories = Category::all();
        $cartCount = collect(session('order_cart', []))->sum('quantity');

        // ===================================================================
        // 🚀 PETICIÓN DE STOCK EN EL ÍNDICE (Punto 1)
        // ===================================================================
        if ($user->branch && $user->branch->external_code) {
            // Llamamos al servicio. El servicio internamente manejará la caché de 10 minutos.
            $inventoryService->getStock($user->branch->external_code);
        }
        // ===================================================================

        return view('orders.create-index', compact('categories', 'cartCount'));
    }

    /**
     * Muestra el formulario con los productos de una categoría específica.
     * @param int $categoryId El ID de la categoría (línea de producción)
     */

    public function create(int $categoryId, InventoryService $inventoryService)
    {
        // 🚨 SEGURIDAD: Solo permitir acceso a usuarios con rol 'manager'
        if (!Auth::check() || Auth::user()->role !== 'manager') {
            return redirect()->route('orders.index')->with('error', 'Acceso denegado a la creación de pedidos.');
        }

        // 1. Obtener la Categoría (usada solo para el título/encabezado)
        $category = Category::find($categoryId);
        if (!$category) {
            return redirect()->route('orders.createIndex')->with('info', 'Categoría no encontrada.');
        }

        $categoryName = $category->name;
        $lineNumber = $categoryId;
        $cart = session('order_cart', []);
        $cartCount = collect($cart)->count();

        // ============================================================
        // 🚀 Obtener LOS PRODUCTOS DIRECTAMENTE desde la API externa
        //      (si la API responde, mostramos SOLO esos productos)
        // ============================================================
        $user = Auth::user();
        $products = [];

        if ($user->branch && $user->branch->external_code) {
            $apiResponse = $inventoryService->getStock($user->branch->external_code);
            $apiData = $apiResponse['data']['bodega'] ?? [];

            foreach ($apiData as $item) {
                $code = $item['codigo_producto'] ?? null;
                $name = $item['nombre'] ?? ($item['product_name'] ?? 'Sin nombre');

                $existencias_apertura = (float)($item['existencias'] ?? 0);
                $entradas_hoy = (float)($item['entradashoy'] ?? 0);
                $salidas_hoy = (float)($item['salidashoy'] ?? 0);
                $stock_en_tiempo_real = $existencias_apertura + $entradas_hoy - $salidas_hoy;

                $quantityInCart = $cart[$code]['quantity'] ?? 0;

                if ($code) {
                    $products[] = [
                        'code' => $code,
                        'name' => $name,
                        'stock' => $stock_en_tiempo_real,
                        'quantity' => $quantityInCart,
                    ];
                }
            }
        }

        // Si la API no devolvió productos, avisamos y no mostramos productos locales
        if (empty($products)) {
            return redirect()->route('orders.createIndex')->with('info', "No se encontraron productos en la API para la línea {$categoryName}.");
        }

        return view('orders.create', [
            'products' => $products,
            'categoryName' => $categoryName,
            'lineNumber' => $lineNumber,
            'cartCount' => $cartCount
        ]);
    }

    /**
     * Añade o actualiza productos de una categoría en la cesta de la sesión.
     * (Traído del archivo NUEVO, ya que usa la lógica de cesta/sesión)
     */
    public function addItem(Request $request, InventoryService $inventoryService)
    {
        // 1. Validar que al menos se haya ingresado una cantidad mayor a 0
        $validatedData = $request->validate([
            'quantities' => 'required|array',
            'quantities.*.quantity' => 'nullable|integer|min:0',
            // El product_code es la llave de la cesta y debe ser string para la búsqueda
            'quantities.*.product_code' => 'required|string',
            'line_number' => 'required|exists:categories,id',
        ]);

        $newCart = session('order_cart', []);
        $itemsAddedCount = 0;

        // Intentar obtener la respuesta de la API (si existe) para extraer nombres
        $user = Auth::user();
        $apiData = [];
        if ($user && $user->branch && $user->branch->external_code) {
            $apiResponse = $inventoryService->getStock($user->branch->external_code);
            $apiData = $apiResponse['data']['bodega'] ?? [];
        }

        foreach ($validatedData['quantities'] as $data) {
            $quantity = (int) $data['quantity'];
            $productCode = $data['product_code'];

            if ($quantity > 0) {
                // Si la cantidad es > 0, añadir o actualizar
                $product = Product::where('product_code', $productCode)->first();

                // Si no existe en la DB, intentar crear un registro mínimo usando la API
                if (!$product) {
                    // Buscar el nombre en los datos de la API (si está disponible)
                    $found = null;
                    foreach ($apiData as $item) {
                        if (($item['codigo_producto'] ?? null) == $productCode) {
                            $found = $item;
                            break;
                        }
                    }

                    $productName = $found['nombre'] ?? $productCode;

                    // Si ya existe un producto con ese nombre, lo reutilizamos
                    $existingByName = Product::where('name', $productName)->first();
                    if ($existingByName) {
                        $product = $existingByName;
                        // Si el producto no tiene product_code, lo actualizamos
                        if (empty($product->product_code)) {
                            $product->product_code = $productCode;
                            $product->save();
                        }
                    } else {
                        // Crear registro mínimo
                        $product = Product::create([
                            'product_code' => $productCode,
                            'name' => $productName,
                            'category_id' => $validatedData['line_number'],
                            'unit' => 'Unidad',
                            'cost' => 0.00,
                            'is_active' => true,
                            'stock' => 0,
                        ]);
                    }
                }

                if ($product) {
                    $newCart[$productCode] = [
                        'product_id' => $product->id,
                        'product_code' => $productCode,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'category_id' => $validatedData['line_number'],
                    ];
                    $itemsAddedCount++;
                }
            } else {
                // Si la cantidad es 0 o menos, eliminar del carrito si existe
                unset($newCart[$productCode]);
            }
        }

        // Si no se añadió ningún ítem en esta página, pero hay ítems en la cesta
        if ($itemsAddedCount === 0 && count($newCart) === 0) {
            return redirect()->route('orders.createIndex')->with('info', 'No se ha añadido ningún producto en esta línea.');
        }

        // Guardar la cesta actualizada en la sesión
        session(['order_cart' => $newCart]);
        $totalItems = collect($newCart)->sum('quantity');

        return redirect()->route('orders.createIndex')
            ->with('success', "");
    }

    /**
     * Almacena el pedido UNIFICADO finalizando el proceso de la cesta.
     * (Traído del archivo NUEVO, con transacciones robustas)
     */
    public function store(Request $request)
    {
        // 1. Obtener la cesta de la sesión
        $cart = session('order_cart', []);

        if (empty($cart)) {
            return redirect()->route('orders.createIndex')->with('error', 'El pedido está vacío. Por favor, añade productos primero.');
        }

        // 2. Validación de usuario y sucursal
        $user = Auth::user();

        if (!$user || !($branchId = $user->branch_id)) {
            Log::error('Intento de crear pedido por usuario sin branch_id.', ['user_id' => $user->id ?? 'Invitado']);
            return redirect()->route('orders.createIndex')->with('error', 'Usuario o Sucursal no asignada. No se puede crear el pedido.');
        }

        // Obtener el category_id del primer ítem del carrito
        $firstItem = reset($cart);
        $categoryId = $firstItem['category_id'] ?? null;

        if (!$categoryId) {
            return redirect()->back()->with('error', 'No se pudo determinar la línea (categoría) del pedido. Intenta vaciar el carrito y empezar de nuevo.');
        }

        // 3. Preparar datos de OrderItem
        $orderItemsData = [];
        foreach ($cart as $item) {
            $quantity = (int) $item['quantity'];
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $costAtOrder = $item['cost'] ?? 0.00;
            $unitUsed = $item['unit'] ?? 'Unidad';

            if ($quantity > 0 && $productId) {
                $orderItemsData[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'cost_at_order' => $costAtOrder,
                    'unit' => $unitUsed,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($orderItemsData)) {
            session()->forget('order_cart');
            return redirect()->route('orders.createIndex')->with('error', 'El pedido final está vacío después de la limpieza de datos.');
        }

        // ------------------------------------------------------------------
        // 💡 INICIO DE LA LÓGICA DEL CORRELATIVO Y TRANSACCIÓN
        // ------------------------------------------------------------------
        try {
            DB::beginTransaction();

            // 4. Generar el Correlativo (SEGURO dentro de la transacción)
            $correlativeService = new CorrelativeService($branchId);
            $correlativeNumber = $correlativeService->getCorrelative();

            if (!$correlativeNumber) {
                // El servicio falla si no encuentra el registro o si excede el límite
                DB::rollBack(); // Deshacemos cualquier bloqueo o cambio
                return redirect()->back()->with('error', 'ERROR: No se pudo generar el número de pedido. Verifique la configuración de Correlativos.');
            }

            // 5. Crear la Orden Principal e incluir el nuevo campo
            $order = Order::create([
                'branch_id' => $branchId,
                'user_id' => $user->id,
                'notes' => $request->input('notes'),
                'status' => 'Pendiente',
                'requested_at' => now(),
                'category_id' => $categoryId,
                'correlative_number' => $correlativeNumber, // <-- CAMPO NUEVO Y ÚNICO
            ]);

            // 6. Asignar order_id a los items
            $finalOrderItems = array_map(function ($item) use ($order) {
                $item['order_id'] = $order->id;
                return $item;
            }, $orderItemsData);

            // 7. Insertar los OrderItems masivamente
            OrderItem::insert($finalOrderItems);

            DB::commit(); // Si todo fue bien, se guarda el pedido Y se incrementa el correlativo.

            // 8. Limpiar la sesión y redirigir
            session()->forget('order_cart');
            return redirect()->route('orders.show', $order)->with('success', '¡Pedido Masivo Creado con Éxito! Número: ' . $correlativeNumber);
        } catch (\Exception $e) {
            DB::rollBack(); // Si algo falla (pedido, correlativo, items), se deshace TODO.
            Log::error('Fallo Crítico al Guardar el Pedido: ' . $e->getMessage() . ' en línea: ' . $e->getLine());

            return redirect()->back()->with('error', 'ERROR DE BASE DE DATOS: Fallo al guardar el pedido. ' . $e->getMessage());
        }
    }
    /**
     * Muestra el formulario para editar un pedido existente.
     */
    public function edit(Order $order, InventoryService $inventoryService)
    {
        // 1. Verificación de Estado (Si no es Pendiente, no se puede editar)
        if (strtolower($order->status) !== 'pendiente') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'No se puede editar un pedido que no esté en estado "Pendiente".');
        }

        // 2. Cargar las relaciones necesarias
        $order->load(['orderItems.product', 'user', 'branch']);

        // ============================================================
        // 🚀 LÓGICA DE STOCK EN TIEMPO REAL (INICIO)
        // ============================================================

        $user = Auth::user();
        $externalStock = [];
        $allProducts = Product::select('id', 'name', 'product_code', 'unit', 'category_id')->get();

        // Obtenemos el stock solo si el usuario tiene una sucursal con código externo
        if ($user->branch && $user->branch->external_code) {
            $apiResponse = $inventoryService->getStock($user->branch->external_code);
            $apiData = $apiResponse['data']['bodega'] ?? [];

            // Procesamos la lista para obtener ['CODIGO' => CANTIDAD REAL]
            foreach ($apiData as $item) {
                $code = $item['codigo_producto'] ?? null;

                // Obtenemos los valores. Usamos (float) para asegurar que se sumen correctamente.
                $existencias_apertura = (float)($item['existencias'] ?? 0);
                $entradas_hoy = (float)($item['entradashoy'] ?? 0);
                $salidas_hoy = (float)($item['salidashoy'] ?? 0);

                // Calculamos el stock en tiempo real
                $stock_en_tiempo_real = $existencias_apertura + $entradas_hoy - $salidas_hoy;

                if ($code) {
                    $externalStock[$code] = $stock_en_tiempo_real; // <--- GUARDAMOS EL VALOR CALCULADO
                }
            }
        }

        // 3. Mapear TODOS los productos del catálogo (PARA EL SELECTOR Y LA BÚSQUEDA)
        $productsWithStock = $allProducts->map(function ($product) use ($externalStock) {
            $realStock = $externalStock[$product->product_code] ?? $product->stock ?? 0;

            // Devolvemos el producto con el stock actualizado
            $product->stock = $realStock;

            return $product;
        });

        // ============================================================
        // 🚀 LÓGICA DE STOCK EN TIEMPO REAL (FIN)
        // ============================================================

        // 4. Prepara los ítems actuales del pedido
        $orderItems = $order->orderItems->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'unit' => $item->product->unit,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // 5. Carga todas las categorías
        $categories = Category::select('id', 'name')->get();

        // 6. Pasamos el catálogo completo al frontend
        return view('orders.edit', compact('order', 'orderItems'))
            ->with([
                'products' => $productsWithStock, // <-- Catálogo COMPLETO CON STOCK REAL
                'categories' => $categories,
            ]);
    }

    /**
     * Actualiza el pedido en la base de datos.
     */
    public function update(Request $request, Order $order)
    {
        // 1. Verificación de Estado
        if (strtolower($order->status) !== 'pendiente') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Error: Solo se pueden editar pedidos con estado "Pendiente".');
        }

        // 2. Validación
        $validatedData = $request->validate([
            'notes' => 'nullable|string|max:500',
            'orderItems' => 'nullable|array',
            'orderItems.*.product_id' => 'required|exists:products,id',
            'orderItems.*.quantity' => 'required|integer|min:1',
        ]);

        // 3. LÓGICA DE ANULACIÓN: Si el array de ítems está vacío después de validar, ANULAR el pedido.
        if (empty($validatedData['orderItems'])) {
            $order->status = 'Anulado';
            $order->save();
            return redirect()->route('orders.show', $order)
                ->with('success', 'El pedido ha sido ANULADO exitosamente ya que se eliminaron todos los productos.');
        }

        try {
            DB::beginTransaction();

            // 4. Actualizar la orden principal (solo notas)
            $order->update([
                'notes' => $validatedData['notes'] ?? null,
            ]);

            // 5. Preparar nuevos items para la base de datos
            $newItems = [];
            foreach ($validatedData['orderItems'] as $itemData) {
                $newItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // 6. Sincronizar (eliminar y recrear) los ítems del pedido
            $order->orderItems()->delete(); // Elimina todos los items existentes
            OrderItem::insert($newItems); // Inserta los nuevos items

            DB::commit();

            return redirect()->route('orders.show', $order)->with('success', '¡Pedido actualizado con éxito!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar el pedido #' . $order->correlative_number . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar el pedido. Revisa los datos y vuelve a intentarlo.');
        }
    }


    // =======================================================
    // --- CRUD ESTÁNDAR ---
    // (Lógica del archivo NUEVO, ya que es más completa)
    // =======================================================

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('orders.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Asegurarse de que los items y las relaciones estén cargados
        $order->load(['orderItems.product', 'user', 'branch']);

        // Autorización para Gerentes: solo pueden ver sus propios pedidos
        if (Auth::user()->role === 'manager' && $order->user_id !== Auth::id()) {
            // NOTA: Esto solo permite al manager ver sus propios pedidos. Si el manager debe ver los pedidos de OTROS usuarios en SU sucursal,
            // la lógica debe ser: && $order->branch_id !== Auth::user()->branch_id (lo que requeriría ajustar el index también).
            // Mantenemos la lógica de la rama NUEVA que es más restrictiva:
            if ($order->user_id !== Auth::id() && $order->branch_id !== Auth::user()->branch_id) {
                return redirect()->route('orders.index')
                    ->with('error', 'No tienes permiso para ver este pedido.');
            }
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Update the status of the specified order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Pendiente', 'Confirmado', 'Anulado'])],
        ]);

        $updateData = ['status' => $validated['status']];

        // Lógica para CONFIRMADO
        if ($validated['status'] === 'Confirmado') {
            $updateData['completed_at'] = now(); // Guarda la fecha/hora actual
            $updateData['confirmed_by_user_id'] = Auth::id(); // Guarda el usuario actual (Admin/Producción)
        }
        // Lógica para PENDIENTE (Si se revierte la acción por error)
        elseif ($validated['status'] === 'Pendiente') {
            $updateData['completed_at'] = null;
            $updateData['confirmed_by_user_id'] = null;
        }
        // Para ANULADO, decidimos si mantenemos la fecha o no (usualmente no es necesario borrarla si ya estaba confirmada, pero aquí reiniciamos para limpieza)
        elseif ($validated['status'] === 'Anulado') {
            // Opcional: Puedes guardar quién anuló si quisieras, pero por ahora solo cambiamos estado
        }

        $order->update($updateData);

        return redirect()->back()->with('success', "Estado del pedido #{$order->correlative_number} actualizado a '{$order->status}'.");
    }

    /**
     * Remove the specified resource from storage.
     * (Lógica del archivo NUEVO, que es más segura)
     */
    public function destroy(Order $order)
    {
        // Solo puede eliminar si el pedido está 'Pendiente' y es su creador (o Admin/Production)
        $user = Auth::user();

        if (strtolower($order->status) !== 'pendiente' && $user->role !== 'admin') {
            return redirect()->back()->with('error', 'Solo se pueden eliminar pedidos pendientes. Contacta a un administrador para más ayuda.');
        }

        // El manager solo puede eliminar sus propios pedidos (o si es admin/production).
        if ($user->role === 'manager' && $order->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Solo puedes eliminar tus propios pedidos.');
        }

        try {
            // Eliminar OrderItems y luego la Order, asumiendo que el Cascade no está activo
            // Si la FK tiene onDelete('cascade') en la migración, OrderItem::where('order_id', $order->id)->delete(); es opcional.
            $order->orderItems()->delete();
            $order->delete();

            return redirect()->route('orders.index')->with('success', 'Pedido eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar pedido: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el pedido. Podría tener dependencias.');
        }
    }

    public function printReport(Order $order)
    {
        // 1. Cargar relaciones (incluimos 'confirmedBy' para obtener el nombre)
        $order->load(['user.branch', 'orderItems.product', 'confirmedBy']);

        // 2. Lógica para el Número de Envío
        $numero_envio = 'N/A';
        if ($order->status !== 'Pendiente') {
            $numero_envio = 'ENV-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
        }

        // 3. Obtener el Usuario que Confirma (CORREGIDO)
        // Si existe la relación confirmedBy, usamos su nombre. Si no, 'N/A'.
        $usuario_confirmacion = $order->confirmedBy->name ?? 'N/A';

        return view('orders.report', compact('order', 'numero_envio', 'usuario_confirmacion'));
    }

    public function downloadReport(Order $order)
    {
        // 1. Cargar datos (Igual que arriba, incluimos 'confirmedBy')
        $order->load(['user.branch', 'orderItems.product', 'confirmedBy']);

        // Lógica Envío
        $numero_envio = 'N/A';
        if ($order->status !== 'Pendiente') {
            $numero_envio = 'ENV-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
        }

        // 2. Lógica Usuario Confirmación (CORREGIDO)
        $usuario_confirmacion = $order->confirmedBy->name ?? 'N/A';

        // 3. Cargar la vista Blade
        $pdf = Pdf::loadView('orders.report', compact('order', 'numero_envio', 'usuario_confirmacion'))
            ->setOption('isRemoteEnabled', true) // IMPORTANTE: true para imágenes
            ->setOption('isPhpEnabled', false)
            ->setOption('chroot', public_path());

        $filename = 'Anthony-' . $order->correlative_number . '.pdf';

        return $pdf->stream($filename);
    }
}
