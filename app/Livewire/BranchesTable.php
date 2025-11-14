<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use Livewire\WithPagination;

class BranchesTable extends Component
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
        $branches = Branch::query()
            // Buscamos en 'name', 'address' y 'phone'
            ->where(function($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('address', 'like', '%'.$this->search.'%')
                      ->orWhere('phone', 'like', '%'.$this->search.'%');
            })
            ->paginate($this->perPage);

        return view('livewire.branches-table', [
            'branches' => $branches,
        ]);
    }
}
