<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth; // <-- ¡Importante para los roles!

class OrdersTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        // Empezamos la consulta cargando las relaciones
        $query = Order::with(['user', 'branch']);

        // --- LÓGICA DE ROLES ---
        // Si el usuario es un gerente, filtramos solo los pedidos de su sucursal
        if ($user->hasRole('manager')) {
            $query->where('branch_id', $user->branch_id);
        }

        // --- LÓGICA DE BÚSQUEDA ---
        // Aplicamos la búsqueda sobre la consulta ya filtrada por rol
        $query->where(function($q) {
            $q->where('id', 'like', $this->search . '%') // Buscar por ID (al inicio)
              ->orWhere('status', 'like', '%' . $this->search . '%') // Buscar por Estado

              // Buscar en la tabla relacionada 'users'
              ->orWhereHas('user', function($userQuery) {
                  $userQuery->where('name', 'like', '%' . $this->search . '%');
              })

              // Buscar en la tabla relacionada 'branches'
              ->orWhereHas('branch', function($branchQuery) {
                  $branchQuery->where('name', 'like', '%' . $this->search . '%');
              });
        });

        // Paginamos el resultado final
        $orders = $query->orderBy('id', 'desc')->paginate($this->perPage);

        return view('livewire.orders-table', [
            'orders' => $orders,
        ]);
    }
}
