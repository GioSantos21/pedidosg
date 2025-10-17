<?php

namespace App\Models;
use App\Models\Branch;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['branch_id', 'user_id', 'status', 'notes', 'completed_at'];

    // Relación de uno a muchos: Un pedido tiene muchos ítems
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relación de muchos a uno: Un pedido pertenece a una sucursal
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relación de muchos a uno: Un pedido fue creado por un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
