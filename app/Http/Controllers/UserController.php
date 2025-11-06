<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

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

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        $branches = Branch::all(); // Necesitamos las sucursales para el dropdown
        return view('admin.users.create', compact('branches'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'production', 'manager'])],
            'branch_id' => [
                Rule::requiredIf($request->role === 'manager'),
                'nullable',
                'exists:branches,id'
            ],
        ]);

        // Prepara los datos para la creación
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Hashear la contraseña
            'role' => $validated['role'],
            'branch_id' => $validated['role'] === 'manager' ? $validated['branch_id'] : null,
        ];

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    // El registro (create/store) lo maneja Breeze.
    // El borrado (destroy) es delicado y lo omitiremos.
}
