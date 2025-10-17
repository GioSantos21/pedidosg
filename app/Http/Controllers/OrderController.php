<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
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
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
