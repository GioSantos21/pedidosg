<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Correlative;
use Livewire\WithPagination;

class CorrelativesTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $correlatives = Correlative::with('branch') // Cargamos la relación sucursal
            ->where(function($query) {
                $query->where('prefix', 'like', '%'.$this->search.'%')
                      ->orWhereHas('branch', function($q) {
                          $q->where('name', 'like', '%'.$this->search.'%');
                      });
            })
            ->paginate($this->perPage);

        return view('livewire.correlatives-table', [
            'correlatives' => $correlatives,
        ]);
    }
}
