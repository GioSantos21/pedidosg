<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    // Este mapeo ya no se usa para consultar, pero se mantiene para la lógica de presentación si es necesario
    const CATEGORY_MAP = [
        1 => 'Panadería',
        2 => 'Pastelería',
        3 => 'Repostería',
    ];

    /**
     * Muestra la lista de pedidos.
     */
    public function index()
    {
        // Se puede filtrar por el branch del usuario si no es admin/producción
        $orders = Order::with(['user', 'branch', 'category'])
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    /**
     * Muestra el índice para seleccionar la línea (categoría) de pedido.
     */
    public function createIndex()
    {
        // Solo para Gerentes (managers)
        if (!Auth::user() || !Auth::user()->hasRole('manager')) {
            return redirect()->route('orders.index')->with('error', 'Solo los gerentes pueden iniciar nuevos pedidos.');
        }

        // Recuperamos todas las categorías disponibles para el índice (o solo las mapeadas)
        $categories = Category::whereIn('id', array_keys(self::CATEGORY_MAP))
                              ->get(['id', 'name']);

        return view('orders.create-index', compact('categories'));
    }

    /**
     * Muestra el formulario para crear un pedido masivo para una categoría específica.
     * @param int $categoryId El ID de la categoría
     */
    public function create(int $categoryId)
    {
        // 1. Autenticación y chequeo de rol
        if (!Auth::user() || !Auth::user()->hasRole('manager')) {
            return redirect()->route('orders.index')->with('error', 'Acceso denegado a la creación de pedidos.');
        }

        // 2. Obtener el nombre de la categoría y verificar su existencia
        $category = Category::find($categoryId);
        if (!$category) {
             return redirect()->route('orders.createIndex')->with('error', 'Línea de categoría no válida.');
        }

        // 3. Obtener todos los productos activos de esa categoría desde la BD
        $products = Product::where('category_id', $categoryId)
                            ->where('is_active', true)
                            ->orderBy('product_code')
                            ->get();

        if ($products->isEmpty()) {
             // Redireccionamos sin error, ya que no es un fallo, sino que no hay productos
             return redirect()->route('orders.createIndex')->with('info', "No hay productos activos disponibles para la línea de {$category->name}.");
        }

        $branch = Auth::user()->branch;

        // 🚨 Mapear la colección de productos al formato que Alpine.js espera (Corregido en paso anterior)
        $mappedProducts = $products->map(function ($product) {
            $currentStock = rand(10, 100); // Valor de stock simulado

            return [
                'id' => $product->id,
                'code' => $product->product_code, // Alpine.js espera 'code'
                'name' => $product->name,
                'unit' => $product->unit,
                'cost' => $product->cost,
                'stock' => $currentStock, // Alpine.js espera 'stock'
            ];
        })->values();

        // 4. Pasar los datos a la vista
        return view('orders.create', [
            'categoryId' => $categoryId,
            'categoryName' => $category->name,
            'products' => $mappedProducts, // Pasamos el array mapeado
            'branch' => $branch,
            'lineNumber' => $categoryId,
        ]);
    }

    /**
     * Almacena un nuevo pedido masivo y sus items.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Pre-validación de usuario
        $user = Auth::user();
        if (!$user || !($branchId = $user->branch_id)) {
            // Este error puede ser la causa de la recarga silenciosa si el usuario no tiene sucursal
            Log::error('Intento de crear pedido por usuario sin branch_id.', ['user_id' => $user->id ?? 'Invitado']);
            return back()->with('error', 'Error de autenticación: Tu cuenta no está asignada a una sucursal o no tienes permisos.')->withInput();
        }

        // 2. Validación de Datos (Ajustada para manejar array de cantidades)
        $validatedData = $request->validate([
            // Usamos 'line_number' para compatibilidad inmediata con tu formulario.
            'line_number' => 'required|integer|exists:categories,id',

            // Validamos que 'quantities' sea un array (no es requerido que haya items aún)
            'quantities' => 'nullable|array',

            // Validamos cada item dentro de quantities
            'quantities.*.product_code' => 'required|string',
            'quantities.*.quantity' => 'nullable|integer|min:0|max:10000',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = $user->id;
        $categoryId = $validatedData['line_number'];
        $quantitiesData = $validatedData['quantities'] ?? []; // Asegurar que sea un array vacío si es nulo

        // 3. Filtrar, verificar y mapear productos solicitados (cantidad > 0)
        $requestedProducts = collect($quantitiesData)
            ->filter(function ($item) {
                // Filtramos por items donde la cantidad sea un número entero > 0
                return isset($item['quantity']) && is_numeric($item['quantity']) && (int) $item['quantity'] > 0;
            });

        if ($requestedProducts->isEmpty()) {
            // Error específico si no hay cantidades solicitadas
            return back()->withErrors(['quantities_general' => 'Debes solicitar al menos un producto con una cantidad mayor a cero.'])->withInput();
        }

        // Obtener los códigos de producto y mapearlos a Product ID (Numérico)
        $productCodes = $requestedProducts->pluck('product_code')->all();

        // Mapear product_code a product_id (esto asegura que los códigos enviados son válidos y obtenemos el ID real)
        $productsMap = Product::whereIn('product_code', $productCodes)->pluck('id', 'product_code')->all();

        if (count($productCodes) !== count($productsMap)) {
            Log::warning('Códigos de producto inválidos en el pedido. Puede ser un intento de inyección.', ['codes_received' => $productCodes, 'codes_found' => array_keys($productsMap)]);
            return back()->with('error', 'Uno o más códigos de producto son inválidos y no existen en la base de datos.')->withInput();
        }

        // 4. Transacción y Guardado
        DB::beginTransaction();

        try {
            // Crea el pedido principal
            $order = Order::create([
                'branch_id' => $branchId,
                'user_id' => $userId,
                'category_id' => $categoryId,
                'notes' => $validatedData['notes'] ?? null,
                'status' => 'Pendiente',
                'requested_at' => now(),
            ]);

            // Prepara los detalles del pedido (OrderItems)
            $orderItemsData = $requestedProducts->map(function ($item) use ($order, $productsMap) {
                $productCode = $item['product_code'];

                return [
                    'order_id' => $order->id,
                    'product_id' => $productsMap[$productCode], // Usamos el ID NUMÉRICO real del producto
                    'quantity' => (int) $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->values()->all();

            // Guarda los detalles en la tabla 'order_items'
            OrderItem::insert($orderItemsData);

            DB::commit();

            $categoryName = Category::find($categoryId)->name ?? 'Categoría Desconocida';
            // 5. Redirección de Éxito
            return redirect()->route('orders.index')->with('success', "¡El pedido masivo de {$categoryName} ha sido creado con éxito!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error FATAL al guardar el pedido: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'request' => $request->all()]);
            return back()->with('error', 'Ocurrió un error interno al guardar el pedido. Contacta a soporte y verifica los logs.')->withInput();
        }
    }

    // Métodos restantes (show, edit, update, destroy, updateStatus) se mantienen igual

    public function show(Order $order)
    {
        // Lógica de permisos
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        // Lógica de permisos
        return view('orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        // Lógica de permisos y validación de update
        $order->update($request->validate(['notes' => 'nullable|string|max:500']));
        return redirect()->route('orders.show', $order)->with('success', 'Pedido actualizado exitosamente.');
    }

    public function destroy(Order $order)
    {
        // Lógica de permisos
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Pedido anulado exitosamente.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        // Lógica de permisos y validación de estado
        $request->validate(['status' => ['required', Rule::in(['Pendiente', 'Confirmado', 'Anulado'])]]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'Confirmado') {
            $updateData['completed_at'] = now();
        } else {
            $updateData['completed_at'] = null;
        }

        $order->update($updateData);
        return redirect()->route('orders.show', $order)->with('success', 'Estado del pedido actualizado a ' . $request->status . '.');
    }
}
