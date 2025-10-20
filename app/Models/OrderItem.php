<?php

namespace App\Models;
use App\Models\Order;
use App\Models\Product;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [ 'product_id', 'quantity'];

    // Relación de muchos a uno: El ítem pertenece a un pedido
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relación de muchos a uno: El ítem corresponde a un producto
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
