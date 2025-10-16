<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'Panadería']);
        Category::create(['name' => 'Repostería']);
        Category::create(['name' => 'Bebidas y Refrescos']);
        Category::create(['name' => 'Insumos de Limpieza']);
        Category::create(['name' => 'Empaques y Desechables']);
    }
}
