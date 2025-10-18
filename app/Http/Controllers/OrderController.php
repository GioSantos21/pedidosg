<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ¡Importante!
use Illuminate\Auth\Access\AuthorizationException; // ¡Importante para la seguridad!

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $query = Order::with(['branch', 'user']);

        // Filtrar por Sucursal si el rol es 'manager'
        if ($user->role === 'manager') {
            $query->where('branch_id', $user->branch_id);
        }

        // Mostrar siempre los pedidos más recientes primero
        $orders = $query->orderByDesc('id')->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (auth()->user()->role !== 'manager') {
            abort(403, 'Solo los gerentes de sucursal pueden crear pedidos.');
        }

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $branchName = auth()->user()->branch->name ?? 'Sucursal sin asignar';

        return view('orders.create', compact('products', 'branchName'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validación de la Solicitud
        $request->validate([
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // 2. Inicio de la Transacción de Base de Datos
        DB::beginTransaction();

        try {
            // 3. Crear el Pedido Maestro (Order)
            $order = Order::create([
                'branch_id' => auth()->user()->branch_id, // Asignado automáticamente
                'user_id' => auth()->id(),              // Usuario autenticado
                'status' => 'pending',                  // Estado inicial
                'notes' => $request->input('notes'),
            ]);

            // 4. Crear los Ítems de Pedido (Detalle)
            $orderItems = [];
            foreach ($request->input('items') as $item) {
                $orderItems[] = new OrderItem([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // Guardar todos los ítems de una vez
            $order->items()->saveMany($orderItems);

            // 5. Finalizar la Transacción
            DB::commit();

            return redirect()->route('orders.show', $order)->with('success', 'Pedido creado exitosamente y enviado a Producción.');

        } catch (\Exception $e) {
            // Si algo falla, revierte todos los cambios
            DB::rollBack();

            // Opcional: Loggea el error
            // \Log::error('Error al crear pedido: ' . $e->getMessage());

            return back()->withInput()->withErrors('Hubo un error al procesar el pedido. Intenta de nuevo.');
        }
        }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // 1. Cargar el detalle del pedido (Items y sus Productos)
        $order->load(['branch', 'user', 'items.product']);

        // 2. Control de Acceso (Autorización)
        $user = auth()->user();

        // El gerente solo puede ver sus propios pedidos de sucursal
        if ($user->role === 'manager' && $order->branch_id !== $user->branch_id) {
            // Lanzar una excepción de autorización (error 403)
            throw new AuthorizationException('No tienes permiso para ver este pedido.');
        }

        // Los roles admin y production pueden ver cualquier pedido

        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        // 1. Restricción de Estado: Solo se puede editar si está PENDIENTE
        if ($order->status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'El pedido N°' . $order->id . ' ya no se puede modificar, ya que su estado es "' . ucfirst($order->status) . '".');
        }

        // 2. Restricción de Autoría: Solo el creador original o un Admin puede editar
        $user = auth()->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            throw new AuthorizationException('Solo el creador del pedido o un Administrador puede editarlo.');
        }

        $products = Product::where('is_active', true)->orderBy('name')->get();

        // Cargar los items actuales en el formato necesario para el formulario
        $currentItems = $order->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
        ])->toJson(); // Convertir a JSON para Alpine.js

        return view('orders.edit', compact('order', 'products', 'currentItems'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        // 1. Restricción de Estado antes de cualquier acción
        if ($order->status !== 'pending') {
             return redirect()->route('orders.show', $order)
                ->with('error', 'El pedido N°' . $order->id . ' ya no se puede actualizar, ya que su estado es "' . ucfirst($order->status) . '".');
        }

        // 2. Restricción de Autoría (Opcional, ya cubierto por edit, pero bueno para seguridad)
        $user = auth()->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            throw new AuthorizationException('Solo el creador del pedido o un Administrador puede actualizarlo.');
        }

        // 3. Validación
        $request->validate([
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar el Pedido Maestro
            $order->update([
                'notes' => $request->input('notes'),
            ]);

            // Eliminar los items antiguos e insertar los nuevos (Método simple de sincronización)
            $order->items()->delete();

            $orderItems = [];
            foreach ($request->input('items') as $item) {
                if (!empty($item['product_id']) && $item['quantity'] > 0) {
                    $orderItems[] = new OrderItem([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'order_id' => $order->id, // Necesario si usamos createMany o saveMany
                    ]);
                }
            }

            $order->items()->saveMany($orderItems);
            DB::commit();

            return redirect()->route('orders.show', $order)->with('success', 'Pedido N°' . $order->id . ' actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al actualizar pedido: " . $e->getMessage());
            return back()->withInput()->withErrors('Hubo un error al actualizar el pedido. Por favor, contacta a soporte.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {

        // 1. Restricción de Estado: Solo se puede eliminar si está PENDIENTE
        if ($order->status !== 'pending') {
             return back()->with('error', 'No se puede eliminar el pedido N°' . $order->id . ' porque su estado es "' . ucfirst($order->status) . '".');
        }

        // 2. Restricción de Autoría: Solo el creador original o un Admin puede eliminar
        $user = auth()->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            throw new AuthorizationException('Solo el creador del pedido o un Administrador puede eliminarlo.');
        }

        // Eliminación en cascada: Los OrderItems se eliminan automáticamente si la migración lo permite
        $orderId = $order->id;
        $order->delete();

        return redirect()->route('orders.index')->with('success', 'Pedido N°' . $orderId . ' y sus ítems eliminados correctamente.');

    }
}
