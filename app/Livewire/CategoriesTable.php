<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination; // Importante para la paginación

class CategoriesTable extends Component
{
    use WithPagination; // Activa la paginación de Livewire

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
        $categories = Category::query()
            // Buscamos en 'name' Y en 'description'
            ->where(function($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->paginate($this->perPage);

        return view('livewire.categories-table', [
            'categories' => $categories,
        ]);
    }
}
