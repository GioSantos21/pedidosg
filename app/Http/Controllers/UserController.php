<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Muestra la lista de usuarios.
     */
    public function index()
    {
        // Cargamos la relación 'branch' para mostrar el nombre de la sucursal
        $users = User::with('branch')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit(User $user)
    {
        // Pasamos todas las sucursales para el dropdown
        $branches = Branch::all();
        return view('admin.users.edit', compact('user', 'branches'));
    }

    /**
     * Actualiza el rol y la sucursal de un usuario.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'production', 'manager'])],

            // 'branch_id' es requerido SOLO SI el rol es 'manager'
            'branch_id' => [
                Rule::requiredIf($request->role === 'manager'),
                'nullable', // Permite ser nulo si no es manager
                'exists:branches,id' // Debe existir en la tabla branches
            ],
        ]);

        // Lógica de negocio: Si el rol no es 'manager',
        // nos aseguramos de que branch_id sea NULL.
        if ($validated['role'] !== 'manager') {
            $validated['branch_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    // NOTA: No usamos create, store, show, o destroy por ahora.
    // El registro (create/store) lo maneja Breeze.
    // El borrado (destroy) es delicado y lo omitiremos.
}
