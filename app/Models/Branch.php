<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Añadido
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory; // Añadido

    // 1. Campos que se pueden llenar
    protected $fillable = ['name', 'address', 'phone', 'is_active','external_code', 'abbreviation'];

    // 2. Relación: Una sucursal tiene muchos usuarios (gerentes)
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // 3. Relación: Una sucursal tiene muchos pedidos
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Calcula un prefijo de 4 letras y añade un número si ya existe (Ej: TERM1).
     * @return string
     */
    public function getUniqueAbbreviation(): string
    {
        $baseAbbrev = strtoupper(substr($this->name, 0, 4));
        $abbreviation = $baseAbbrev;
        $counter = 1;

        // Comprobamos si ya existe en la base de datos (excluyendo el registro actual)
        while (Branch::where('abbreviation', $abbreviation)
                      ->where('id', '!=', $this->id)
                      ->exists()) {

            // Si el prefijo ya existe, añadimos un número al final (Ej: TERM -> TERM1 -> TERM2)
            $abbreviation = $baseAbbrev . $counter;
            $counter++;

            // Límitamos el prefijo a 5 caracteres (4 letras + 1 dígito) si se usa el contador
            if (strlen($abbreviation) > 10) {
                // Esto puede fallar si tienes demasiadas sucursales con el mismo nombre
                throw new \Exception("Demasiadas sucursales con nombres similares para generar un prefijo único.");
            }
        }

        return $abbreviation;
    }
}
