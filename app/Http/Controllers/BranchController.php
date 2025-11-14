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
        return view('admin.branches.index');
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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $validatedData['is_active'] = $request->has('is_active');
        Branch::create($validatedData);

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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $validatedData['is_active'] = $request->has('is_active');
        $branch->update($validatedData);

        return redirect()->route('admin.branches.index')->with('success', 'Sucursal actualizada exitosamente.');
    }

    /**
     * Elimina la sucursal.
     */
    public function toggleStatus(Branch $branch)
    {
        // Seguridad: No desactivar si tiene usuarios activos asignados
        if ($branch->is_active && $branch->users()->count() > 0) {
             return redirect()->route('admin.branches.index')->with('error', 'No se puede desactivar la sucursal porque tiene usuarios asignados.');
        }

        $branch->is_active = !$branch->is_active;
        $branch->save();

        $status = $branch->is_active ? 'activa' : 'inactiva';
        return redirect()->route('admin.branches.index')->with('success', "La sucursal '{$branch->name}' ha sido marcada como {$status}.");
    }
}
