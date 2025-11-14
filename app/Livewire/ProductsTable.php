<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination; // <-- Importante para la paginación

class ProductsTable extends Component
{
    use WithPagination; // <-- Activa la paginación de Livewire

    public $perPage = 10; // Valor por defecto del selector
    public $search = ''; // Valor del campo de búsqueda

    /**
     * Este método se ejecuta cada vez que $search cambia.
     * Resetea la paginación a la página 1.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Este método se ejecuta cada vez que $perPage cambia.
     */
    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * El método render() es como el 'index' de un controlador.
     */
    public function render()
    {
        $products = Product::with('category')
            ->where(function($query) {
                // Buscamos en 'name' Y en 'category.name'
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhereHas('category', function($q) {
                          $q->where('name', 'like', '%'.$this->search.'%');
                      });
            })
            ->paginate($this->perPage); // Paginamos con el valor del selector

        return view('livewire.products-table', [
            'products' => $products,
        ]);
    }
}
