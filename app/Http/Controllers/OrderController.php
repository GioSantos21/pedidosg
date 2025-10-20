<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     * Lista los pedidos según el rol del usuario.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Order::with(['user.branch', 'branch', 'items.product']);

        if ($user->role === 'manager') {
            // Un gerente solo ve los pedidos de su propia sucursal.
            if ($user->branch_id === null) {
                // Si no tiene sucursal asignada, no ve pedidos.
                return view('orders.index', ['orders' => collect([])]);
            }
            $query->where('branch_id', $user->branch_id);
        }

        // Paginamos los resultados para mejorar el rendimiento
        $orders = $query->latest()->paginate(15);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     * Muestra el formulario para crear un pedido.
     */
    public function create()
    {
        $user = Auth::user();

        // El gerente debe tener una sucursal asignada para poder crear un pedido.
        if ($user->branch_id === null) {
             return redirect()->route('dashboard')
                             ->with('error', 'No tienes una sucursal asignada. Por favor, contacta a tu administrador para poder realizar pedidos.');
        }

        $branchName = $user->branch->name ?? 'Sucursal sin asignar';
        $products = Product::orderBy('name')->get();

        return view('orders.create', compact('products', 'branchName'));
    }

    /**
     * Store a newly created resource in storage.
     * Guarda un nuevo pedido y sus detalles (ítems) en una transacción.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Verificar si el usuario puede crear el pedido (tiene sucursal asignada)
        if ($user->branch_id === null) {
            return back()->with('error', 'Error de Seguridad: Tu usuario no tiene una sucursal válida asignada.');
        }

        // 2. Validación de los datos
        $validatedData = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'], // Debe ser un ID de producto existente
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.min' => 'Debes añadir al menos un producto al pedido.',
            'items.*.product_id.required' => 'Debes seleccionar un producto para cada ítem.',
            'items.*.product_id.exists' => 'El producto seleccionado no es válido.',
            'items.*.quantity.required' => 'Debes especificar la cantidad para cada producto.',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1.'
        ]);


        try {
            DB::beginTransaction();

            // 3. Crear el pedido maestro (Order)
            $order = Order::create([
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'notes' => $validatedData['notes'],
                'status' => 'Pendiente', // Siempre comienza como pendiente
            ]);

            // 4. Crear los ítems del pedido (OrderItems)
            $orderItems = [];
            foreach ($validatedData['items'] as $item) {
                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    // NOTA: 'order_id' es añadido automáticamente por createMany
                ];
            }

            // Usar la relación para guardar múltiples ítems a la vez
            $order->items()->createMany($orderItems);

            DB::commit();

            return redirect()->route('orders.show', $order)->with('success', '¡Pedido registrado con éxito! Pendiente de ser procesado por Producción.');

        } catch (\Exception $e) {
            DB::rollBack();
            // --- CAMBIO CLAVE PARA DEBUGGING ---
            // En lugar de un mensaje genérico, mostramos el error exacto para diagnosticarlo.
            \Log::error("Error al guardar pedido: " . $e->getMessage());

            // MUESTRA EL MENSAJE DE ERROR DIRECTAMENTE:
            return back()->with('error', 'ERROR DE LA BASE DE DATOS: ' . $e->getMessage())->withInput();
            // --- FIN DEL CAMBIO CLAVE ---
        }
    }

    /**
     * Display the specified resource.
     * Muestra los detalles de un pedido.
     */
    public function show(Order $order)
    {
        // Se carga con eager loading para evitar múltiples consultas
        $order->load(['user.branch', 'branch', 'items.product']);

        // Control de acceso: Solo el gerente de la sucursal, Admin o Producción pueden ver.
        if (Auth::user()->role === 'manager' && Auth::user()->branch_id !== $order->branch_id) {
            abort(403, 'Acceso denegado. Solo puedes ver pedidos de tu sucursal.');
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     * Muestra el formulario para editar un pedido.
     */
    public function edit(Order $order)
    {
        // Restricción: Solo si el pedido está PENDIENTE
        if ($order->status !== 'Pendiente') {
            return redirect()->route('orders.show', $order)->with('error', 'Solo se pueden editar pedidos con estado PENDIENTE.');
        }

        // Control de acceso: Solo el gerente que lo creó o un administrador
        if (Auth::user()->role === 'manager' && Auth::user()->id !== $order->user_id) {
             abort(403, 'Acceso denegado. Solo puedes editar pedidos que registraste.');
        }

        $products = Product::orderBy('name')->get();
        // Preparamos los ítems actuales para la vista de Alpine.js/Blade
        $currentItems = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ];
        })->toJson();

        $branchName = $order->branch->name ?? 'N/A';

        return view('orders.edit', compact('order', 'products', 'currentItems', 'branchName'));
    }

    /**
     * Update the specified resource in storage.
     * Actualiza el pedido (solo si está PENDIENTE)
     */
    public function update(Request $request, Order $order)
    {
        // Restricción: Solo si el pedido está PENDIENTE
        if ($order->status !== 'Pendiente') {
            return redirect()->route('orders.show', $order)->with('error', 'Solo se pueden modificar pedidos con estado PENDIENTE.');
        }

        $validatedData = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.min' => 'Debes añadir al menos un producto al pedido.',
            // ... (mensajes de validación)
        ]);

        try {
            DB::beginTransaction();

            // 1. Actualizar el encabezado
            $order->update([
                'notes' => $validatedData['notes'],
            ]);

            // 2. Eliminar ítems antiguos
            $order->items()->delete();

            // 3. Crear los ítems nuevos
            $orderItems = [];
            foreach ($validatedData['items'] as $item) {
                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ];
            }
            $order->items()->createMany($orderItems);

            DB::commit();

            return redirect()->route('orders.show', $order)->with('success', 'Pedido actualizado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            // --- CAMBIO CLAVE PARA DEBUGGING ---
            \Log::error("Error al actualizar pedido: " . $e->getMessage());
            return back()->with('error', 'ERROR DE LA BASE DE DATOS: ' . $e->getMessage())->withInput();
            // --- FIN DEL CAMBIO CLAVE ---
        }
    }

    /**
     * Método dedicado para que Producción cambie el estado del pedido.
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Control de roles: Solo admin y production pueden cambiar el estado.
        if (!Auth::user()->hasRole(['admin', 'production'])) {
             abort(403, 'Acceso denegado. No tienes permisos para cambiar el estado.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Pendiente', 'Confirmado', 'Anulado'])],
        ]);

        $status = $validated['status'];
        $updateData = ['status' => $status];

        // Si el estado es 'completed', registrar el tiempo de finalización
        if ($status === 'Confirmado') {
            $updateData['completed_at'] = now();
        } elseif ($status !== 'Confirmado' && $order->completed_at !== null) {
            // Si el estado vuelve a ser no-completado, limpia el timestamp
            $updateData['completed_at'] = null;
        }

        try {
            $order->update($updateData);

            return back()->with('success', "El estado del pedido #{$order->id} se ha cambiado a {$status}.");
        } catch (\Exception $e) {
            \Log::error("Error al actualizar estado del pedido: " . $e->getMessage());
            return back()->with('error', 'Error al actualizar el estado del pedido.');
        }
    }


    /**
     * Remove the specified resource from storage.
     * Elimina el pedido (solo si está PENDIENTE)
     */
    public function destroy(Order $order)
    {
        // Restricción: Solo si el pedido está PENDIENTE
        if ($order->status !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden eliminar pedidos con estado PENDIENTE.');
        }

        // Control de acceso: Solo el gerente que lo creó o un administrador
        if (Auth::user()->role === 'manager' && Auth::user()->id !== $order->user_id) {
             abort(403, 'Acceso denegado. Solo puedes eliminar pedidos que registraste.');
        }

        try {
            // Se eliminan los items en cascada por la configuración de la base de datos (o se podría hacer aquí)
            $order->delete();
            return redirect()->route('orders.index')->with('success', 'Pedido eliminado correctamente.');
        } catch (\Exception $e) {
            \Log::error("Error al eliminar pedido: " . $e->getMessage());
            return back()->with('error', 'Hubo un error al intentar eliminar el pedido.');
        }
    }
}
