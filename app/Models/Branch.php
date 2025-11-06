<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Añadido
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory; // Añadido

    // 1. Campos que se pueden llenar
    protected $fillable = ['name', 'address', 'phone', 'is_active'];

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
}
