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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create($request->all());

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
        $request->validate([
            // La validación unique debe ignorar la categoría actual
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    // Elimina una categoría
    public function destroy(Category $category)
    {
        // NOTA: Si una categoría tiene productos, la restricción de clave foránea
        // podría causar un error. Considera eliminar primero los productos o reasignarlos.
        try {
            $category->delete();
            return redirect()->route('admin.categories.index')->with('success', 'Categoría eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.categories.index')->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }
    }
}
