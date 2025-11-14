<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

class UsersTable extends Component
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
        $users = User::with('branch') // Cargar la relación 'branch'
            ->where(function($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%')
                      ->orWhere('role', 'like', '%'.$this->search.'%')
                      // Buscar en la tabla relacionada 'branches'
                      ->orWhereHas('branch', function($q) {
                          $q->where('name', 'like', '%'.$this->search.'%');
                      });
            })
            ->paginate($this->perPage);

        return view('livewire.users-table', [
            'users' => $users,
        ]);
    }
}
