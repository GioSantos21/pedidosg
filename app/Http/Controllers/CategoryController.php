<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Muestra la lista de categorías (Ya implementado, se mantiene)
    public function index()
    {
        $categories = Category::paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    // Muestra el formulario para crear una nueva categoría
    public function create()
    {
        return view('admin.categories.create');
    }

    // Almacena una nueva categoría
   // Almacena una nueva categoría
    public function store(Request $request)
    {
        // 1. Validamos los campos que ya tenías
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        // 2. AÑADIMOS LA LÓGICA DEL CHECKBOX
        // $request->has('is_active') devuelve 'true' si el checkbox estaba marcado,
        // y 'false' si no lo estaba.
        // Guardamos este valor (true/false) en nuestro array de datos validados.
        $validatedData['is_active'] = $request->has('is_active');

        // 3. Creamos la categoría usando el array $validatedData
        // (que ahora SÍ tiene el valor de 'is_active').
        // Ya no usamos $request->all().
        Category::create($validatedData);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada exitosamente.');
    }

    // Muestra el formulario para editar una categoría existente
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // Actualiza la categoría en la base de datos
    public function update(Request $request, Category $category)
    {
        // 1. Validamos como ya hacías
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        // 2. AÑADIMOS LA LÓGICA DEL CHECKBOX (igual que en store)
        $validatedData['is_active'] = $request->has('is_active');

        // 3. Actualizamos la categoría con los datos completos
        $category->update($validatedData);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    // Cambiar el status a una categoría
    /**
     * Cambia el estado (activo/inactivo) de una categoría.
     */
    public function toggleStatus(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'activa' : 'inactiva';
        return redirect()->route('admin.categories.index')->with('success', "La categoría '{$category->name}' ha sido marcada como {$status}.");
    }
}
