<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    $this->call([
        BranchSeeder::class,
        CategorySeeder::class,
    ]);

    // Crear un Administrador (role=admin)
    User::create([
        'name' => 'Anthony Admin',
        'email' => 'admin@anthonys.com',
        'password' => Hash::make('password'), // Contraseña: password
        'role' => 'admin',
        'branch_id' => 1, // Asignado a Casa Matriz (Branch ID 1)
    ]);
    // Crear un Gerente de Sucursal (role=manager)
    User::create([
        'name' => 'Gerente Norte',
        'email' => 'gerente@anthonys.com',
        'password' => Hash::make('password'),
        'role' => 'manager',
        'branch_id' => 2, // Asignado a Sucursal Zona Norte (Branch ID 2)
    ]);
}
}
