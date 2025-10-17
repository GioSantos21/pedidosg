<?php

namespace App\Models;
use App\Models\Order;
use App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity'];

    // Relación de muchos a uno: El ítem pertenece a un pedido
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relación de muchos a uno: El ítem corresponde a un producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
