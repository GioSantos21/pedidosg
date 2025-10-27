<?php

namespace App\Http\Controllers; // <-- CORREGIDO

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Muestra la lista de sucursales.
     */
    public function index()
    {
        $branches = Branch::paginate(10);
        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Muestra el formulario para crear una nueva sucursal.
     */
    public function create()
    {
        return view('admin.branches.create');
    }

    /**
     * Almacena una nueva sucursal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Branch::create($request->all());

        return redirect()->route('admin.branches.index')->with('success', 'Sucursal creada exitosamente.');
    }

    /**
     * Muestra el formulario para editar una sucursal.
     */
    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    /**
     * Actualiza la sucursal.
     */
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $branch->update($request->all());

        return redirect()->route('admin.branches.index')->with('success', 'Sucursal actualizada exitosamente.');
    }

    /**
     * Elimina la sucursal.
     */
    public function destroy(Branch $branch)
    {
        if ($branch->users()->count() > 0 || $branch->orders()->count() > 0) {
            return redirect()->route('admin.branches.index')->with('error', 'No se puede eliminar la sucursal porque tiene usuarios o pedidos asociados.');
        }

        try {
            $branch->delete();
            return redirect()->route('admin.branches.index')->with('success', 'Sucursal eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.branches.index')->with('error', 'Ocurrió un error al eliminar la sucursal.');
        }
    }
}
