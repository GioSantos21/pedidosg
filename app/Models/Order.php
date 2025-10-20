<?php

namespace App\Models;
use App\Models\Branch;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['branch_id', 'user_id', 'status', 'notes', 'completed_at'];

     /**
     * Relación: El pedido pertenece a una Sucursal.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación: El pedido fue registrado por un Usuario (Gerente).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un pedido tiene muchos ítems (detalle).
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
