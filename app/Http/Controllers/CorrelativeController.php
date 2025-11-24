<?php

namespace App\Http\Controllers;

use App\Models\Correlative;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CorrelativeController extends Controller
{
    public function index()
    {
        return view('admin.correlatives.index');
    }

    public function create()
    {
        // Solo mostramos sucursales que NO tengan ya un correlativo asignado
        // (Porque la relación es 1 a 1)
        $existingBranches = Correlative::pluck('branch_id');
        $branches = Branch::whereNotIn('id', $existingBranches)->get();

        return view('admin.correlatives.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|unique:correlatives,branch_id',
            'prefix' => 'required|string|max:10|unique:correlatives,prefix',
            'initial' => 'required|integer|min:0',
            'final' => 'required|integer|gt:initial',
            'counter' => 'required|integer|min:0',
        ]);

        Correlative::create($request->all());

        return redirect()->route('admin.correlatives.index')->with('success', 'Configuración creada correctamente.');
    }

    public function edit(Correlative $correlative)
    {
        $branches = Branch::all();
        return view('admin.correlatives.edit', compact('correlative', 'branches'));
    }

    public function update(Request $request, Correlative $correlative)
    {
        $request->validate([
            // Permitimos el mismo branch_id solo si es este registro
            'branch_id' => ['required', Rule::unique('correlatives')->ignore($correlative->id)],
            'prefix' => ['required', 'string', 'max:10', Rule::unique('correlatives')->ignore($correlative->id)],
            'initial' => 'required|integer|min:0',
            'final' => 'required|integer|gt:initial',
            'counter' => 'required|integer|min:0',
        ]);

        $correlative->update($request->all());

        return redirect()->route('admin.correlatives.index')->with('success', 'Correlativo actualizado correctamente.');
    }

    public function toggleStatus($id)
    {
        // 1. Buscar el registro
        $correlative = Correlative::findOrFail($id);

        // 2. Cambiar el estado (si es true pasa a false, y viceversa)
        // Suponiendo que tu columna se llama 'is_active' (booleano)
        $correlative->is_active = !$correlative->is_active;

        // Si usas un status string ('activo', 'inactivo'):
        // $correlative->status = $correlative->status === 'activo' ? 'inactivo' : 'activo';

        // 3. Guardar
        $correlative->save();

        // 4. Redireccionar con mensaje
        $estado = $correlative->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "El correlativo ha sido $estado correctamente.");
    }
}
