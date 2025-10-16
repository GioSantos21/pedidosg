<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create(['name' => 'Casa Matriz - Producción', 'address' => 'Centro Principal']);
        Branch::create(['name' => 'Sucursal Zona Norte', 'address' => 'Av. Norte 123']);
        Branch::create(['name' => 'Sucursal El Centro', 'address' => 'Calle Principal 45']);
    }
}
