<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Muestra la lista de productos (Ya implementado, se mantiene)
    public function index()
    {
        $products = Product::with('category')->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    // Muestra el formulario para crear un nuevo producto
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // Almacena un nuevo producto
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'category_id' => 'required|exists:categories,id',
            'unit' => 'required|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Product::create($request->all());

        return redirect()->route('admin.products.index')->with('success', 'Producto creado exitosamente.');
    }

    // Muestra el formulario para editar un producto existente
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // Actualiza el producto en la base de datos
    public function update(Request $request, Product $product)
    {
        $request->validate([
            // Ignorar el producto actual para la validación unique
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'unit' => 'required|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // Asegurar que el campo is_active se maneje correctamente si no se marca en el formulario
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    // Elimina un producto
    public function destroy(Product $product)
    {
        $product->delete(); // Gracias a las claves foráneas, esto no debería causar problemas
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
