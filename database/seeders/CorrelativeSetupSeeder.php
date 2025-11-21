<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Correlative;

class CorrelativeSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener todas las sucursales existentes (asumo que ya tienes 6 en tu tabla)
        $branches = Branch::all();

        // 2. Recorrer cada sucursal para generar la abreviatura y crear el correlativo
        foreach ($branches as $branch) {
            // A. Generar y guardar la abreviatura única (Ej: TERM1)
            $abbreviation = $branch->getUniqueAbbreviation();
            $branch->abbreviation = $abbreviation;
            $branch->save();

            // B. Crear el registro inicial en la tabla 'correlatives'
            // NOTA: Asumimos un contador inicial de 0 para empezar con '000001'
            Correlative::create([
                'branch_id' => $branch->id,
                // El prefijo final es 'PED-' + Abreviatura + '-' (Ej: PED-TERM-)
                'prefix' => 'PED-' . $abbreviation . '-',
                'initial' => 1,
                'final' => 999999, // Límite de 6 dígitos
                'counter' => 0, // Inicia en 0 para que el primer pedido sea 1
                'counter_record' => 0,
            ]);
        }
    }
}
