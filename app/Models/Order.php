<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Define los campos que pueden ser llenados masivamente.
     * Añade 'requested_at' si lo tienes en tu migración. Si no, solo created_at/updated_at son automáticos.
     */
    protected $fillable = [
        'branch_id',
        'user_id',
        'notes',
        'status',
        'completed_at', // Para registrar cuándo se finaliza
        // Si tienes requested_at en tu migración:
        'requested_at',
    ];

    /**
     * Define las columnas que deben ser convertidas a tipos nativos.
     * Es crucial para que las fechas sean objetos Carbon y se pueda usar ->format().
     */
    protected $casts = [
        'requested_at' => 'datetime', // Convierte la columna a objeto Carbon
        'completed_at' => 'datetime', // Si existe, también debe ser Carbon
    ];

    /**
     * Relación con la Sucursal (Branch)
     */
    public function branch()
    {
        // Asumiendo que branch_id apunta a la tabla 'branches'
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con el Usuario (User) que creó el pedido
     */
    public function user()
    {
        // Asumiendo que user_id apunta a la tabla 'users'
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los Items del Pedido
     */
    public function items()
    {
        // Asumiendo que tienes un modelo OrderItem
        return $this->hasMany(OrderItem::class);
    }
}
