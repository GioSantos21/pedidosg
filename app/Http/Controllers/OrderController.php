<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // Mapeo para identificar las categorías por el número de línea (Punto 5 y 6)
    // El endpoint real usará este número de línea para filtrar.
    const CATEGORY_MAP = [
        1 => 'Panadería',
        2 => 'Pastelería',
        3 => 'Repostería',
    ];

    // Función de simulación de productos (Recibe el número de línea)
    private function simulateProducts(int $lineNumber)
    {
        // Generamos TODOS los productos como si vinieran de un único endpoint
        $allProducts = collect([
            // LINEA 1: Panadería
            ['code' => 'P-001', 'name' => 'Pan Baguette Francés', 'unit' => 'Unidad', 'current_stock' => 50, 'linea' => 1],
            ['code' => 'P-002', 'name' => 'Pan de Molde Integral', 'unit' => 'Unidad', 'current_stock' => 120, 'linea' => 1],
            ['code' => 'P-003', 'name' => 'Croissant', 'unit' => 'Unidad', 'current_stock' => 80, 'linea' => 1],
            ['code' => 'P-004', 'name' => 'Pan de Centeno', 'unit' => 'Unidad', 'current_stock' => 45, 'linea' => 1],
            ['code' => 'P-005', 'name' => 'Pan de Hamburguesa Semilla', 'unit' => 'Docena', 'current_stock' => 20, 'linea' => 1],
            ['code' => 'P-006', 'name' => 'Bollo de Maíz', 'unit' => 'Unidad', 'current_stock' => 60, 'linea' => 1],
            // LINEA 2: Pastelería
            ['code' => 'PST-01', 'name' => 'Tarta de Chocolate (10p)', 'unit' => 'Unidad', 'current_stock' => 15, 'linea' => 2],
            ['code' => 'PST-02', 'name' => 'Cheesecake de Frutos Rojos (8p)', 'unit' => 'Unidad', 'current_stock' => 8, 'linea' => 2],
            ['code' => 'PST-03', 'name' => 'Muffins de Vainilla', 'unit' => 'Docena', 'current_stock' => 30, 'linea' => 2],
            // LINEA 3: Repostería
            ['code' => 'R-01', 'name' => 'Galletas de Mantequilla', 'unit' => 'Kg', 'current_stock' => 10, 'linea' => 3],
            ['code' => 'R-02', 'name' => 'Brownie Fudge', 'unit' => 'Bandeja', 'current_stock' => 5, 'linea' => 3],
            ['code' => 'R-03', 'name' => 'Masa de Hojaldre', 'unit' => 'Paquete', 'current_stock' => 18, 'linea' => 3],
        ]);

        // Filtramos por la línea solicitada, como haría el endpoint
        return $allProducts->where('linea', $lineNumber)->values()->toArray();
    }

    public function index()
    {
        $orders = Order::with(['user', 'branch'])->latest()->paginate(15);
        return view('orders.index', compact('orders'));
    }

    public function createIndex()
    {
        // Solo para Gerentes (managers)
        if (!Auth::user()->hasRole('manager')) {
            return redirect()->route('orders.index')->with('error', 'Solo los gerentes pueden iniciar nuevos pedidos.');
        }

        return view('orders.create-index');
    }

    public function create(int $lineNumber)
    {
        // Solo para Gerentes
        if (!Auth::user()->hasRole('manager')) {
            return redirect()->route('orders.index')->with('error', 'Acceso denegado a la creación de pedidos.');
        }

        // Verificamos si la línea es válida
        if (!isset(self::CATEGORY_MAP[$lineNumber])) {
             return redirect()->route('orders.createIndex')->with('error', 'Línea de categoría no válida.');
        }

        $branch = Auth::user()->branch; // Asume que el usuario tiene una relación 'branch'
        $products = $this->simulateProducts($lineNumber); // Obtenemos productos filtrados

        if (empty($products)) {
             return redirect()->route('orders.createIndex')->with('error', 'No hay productos disponibles para esta línea.');
        }


        $categoryName = self::CATEGORY_MAP[$lineNumber];

        // Pasamos los datos a la vista
        return view('orders.create', compact('branch', 'products', 'categoryName', 'lineNumber'));
    }

    public function store(Request $request)
    {
        // 1. Validación (Ajustamos para recibir el array de cantidades y el número de línea)
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'quantities' => 'required|array',
            'quantities.*' => 'nullable|integer|min:1',
            'line_number' => 'required|integer', // Validamos el número de línea
        ]);

        // 2. Filtrar solo las cantidades mayores a cero
        $quantities = array_filter($validated['quantities'], fn($q) => $q > 0);

        if (empty($quantities)) {
            return redirect()->back()->withInput()->with('error', 'Debe solicitar al menos un producto.');
        }

        // 3. Crear el Pedido
        $order = DB::transaction(function () use ($request, $quantities) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'branch_id' => Auth::user()->branch_id,
                'notes' => $request->notes,
                'status' => 'Pendiente',
                // NOTA: En un sistema real, guardaríamos el número de línea en la tabla 'orders'
                // si fuera importante, pero por ahora solo lo usamos para simular el inventario.
            ]);

            $orderItems = [];
            // Re-simulamos los productos para asegurar que tenemos los nombres y códigos
            $simulatedProducts = collect($this->simulateProducts($request->line_number))->keyBy('code');

            foreach ($quantities as $productCode => $quantity) {
                $productData = $simulatedProducts->get($productCode);

                if ($productData) {
                    $orderItems[] = new OrderItem([
                        'order_id' => $order->id,
                        'product_id' => $productCode, // Usamos el código como ID temporal
                        'quantity' => $quantity,
                    ]);
                }
            }

            $order->items()->saveMany($orderItems);
            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Pedido creado y enviado exitosamente.');
    }

    public function show(Order $order)
    {
        $allowedRoles = ['admin', 'production'];
        if (!Auth::user()->hasAnyRole($allowedRoles) && Auth::user()->branch_id !== $order->branch_id) {
            return redirect()->route('orders.index')->with('error', 'No tienes permiso para ver este pedido.');
        }

        // Como el item->product_id es el CÓDIGO en esta simulación, la vista show DEBE
        // buscar la información del producto usando ese código. Esto lo haremos en la vista show.

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $allowedRoles = ['manager', 'admin', 'production'];
        if (!Auth::user()->hasAnyRole($allowedRoles) || $order->status !== 'Pendiente') {
            return redirect()->route('orders.show', $order)->with('error', 'No se puede editar el pedido.');
        }

        // La edición del pedido masivo es compleja sin un modelo Product real.
        // En esta simulación, la vista de edición solo permitirá actualizar las notas del pedido.
        return view('orders.edit', compact('order'));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $allowedRoles = ['manager', 'admin', 'production'];
        if (!Auth::user()->hasAnyRole($allowedRoles) || $order->status !== 'Pendiente') {
            return redirect()->route('orders.show', $order)->with('error', 'No se puede actualizar el pedido.');
        }

        // Solo actualizamos notas en la simulación de edición.
        $order->update($request->validated());

        return redirect()->route('orders.show', $order)->with('success', 'Pedido actualizado exitosamente.');
    }

    public function destroy(Order $order)
    {
        if ((Auth::user()->id !== $order->user_id || $order->status !== 'Pendiente') && !Auth::user()->hasRole('admin')) {
            return redirect()->route('orders.index')->with('error', 'No tienes permiso para anular este pedido.');
        }

        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Pedido anulado exitosamente.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        if (!Auth::user()->hasRole(['admin', 'production'])) {
            return redirect()->route('orders.show', $order)->with('error', 'No tienes permiso para cambiar el estado.');
        }

        $request->validate(['status' => 'required|in:Pendiente,Confirmado,Anulado']);

        $updateData = [
            'status' => $request->status,
        ];

        if ($request->status === 'Confirmado') {
            $updateData['completed_at'] = now();
        } else {
            $updateData['completed_at'] = null;
        }

        $order->update($updateData);

        return redirect()->route('orders.show', $order)->with('success', 'Estado del pedido actualizado a ' . $request->status . '.');
    }
}
