<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category; // Asume que tienes un modelo Category

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener los IDs de las categorías por su nombre
        $categoryIds = Category::pluck('id', 'name')->all();

        // Si faltan categorías, lanzamos una advertencia
        if (empty($categoryIds)) {
            $this->command->warn('¡ADVERTENCIA! No se encontraron categorías. Ejecuta CategorySeeder primero.');
            return;
        }

        $products = [
            // CATEGORIA: Panadería (Asumiendo que categoryIds['Panadería'] existe)
            [
                'category_id' => $categoryIds['Panadería'] ?? null,
                'product_code' => 'P-001', 'name' => 'Pan Baguette Francés', 'unit' => 'Unidad', 'cost' => 0.50, 'is_active' => 1, 'stock' => 100,
            ],
            [
                'category_id' => $categoryIds['Panadería'] ?? null,
                'product_code' => 'P-002', 'name' => 'Pan de Molde Integral', 'unit' => 'Unidad', 'cost' => 1.50, 'is_active' => 1, 'stock' => 100,
            ],
            [
                'category_id' => $categoryIds['Panadería'] ?? null,
                'product_code' => 'P-003', 'name' => 'Croissant', 'unit' => 'Unidad', 'cost' => 0.80, 'is_active' => 1, 'stock' => 100,
            ],
            // CATEGORIA: Pastelería
            [
                'category_id' => $categoryIds['Pastelería'] ?? null,
                'product_code' => 'PST-01', 'name' => 'Tarta de Chocolate (10p)', 'unit' => 'Unidad', 'cost' => 15.00, 'is_active' => 1 , 'stock' => 100,
            ],
            [
                'category_id' => $categoryIds['Pastelería'] ?? null,
                'product_code' => 'PST-02', 'name' => 'Cheesecake de Frutos Rojos (8p)', 'unit' => 'Unidad', 'cost' => 12.00, 'is_active' => 1 ,  'stock' => 100,
            ],
            [
                'category_id' => $categoryIds['Pastelería'] ?? null,
                'product_code' => 'PST-03', 'name' => 'Muffins de Vainilla', 'unit' => 'Docena', 'cost' => 8.00, 'is_active' => 1, 'stock' => 100,
            ],
            // CATEGORIA: Repostería
            [
                'category_id' => $categoryIds['Repostería'] ?? null,
                'product_code' => 'R-01', 'name' => 'Galletas de Mantequilla', 'unit' => 'Kg', 'cost' => 5.00, 'is_active' => 1,  'stock' => 100,
            ],
            [
                'category_id' => $categoryIds['Repostería'] ?? null,
                'product_code' => 'R-02', 'name' => 'Brownie Fudge', 'unit' => 'Bandeja', 'cost' => 7.50, 'is_active' => 1,  'stock' => 100,
            ],
        ];

        // 2. Insertar solo los productos que tienen un category_id válido
        $validProducts = collect($products)->filter(fn($p) => $p['category_id'] !== null)->toArray();

        DB::table('products')->insert($validProducts);

        $this->command->info('Productos de prueba insertados con éxito.');
    }
}
