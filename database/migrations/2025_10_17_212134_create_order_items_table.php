<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Clave foránea al Pedido
            $table->foreignId('product_id')->constrained()->onDelete('cascade');; // Producto solicitado
            $table->integer('quantity'); // Cantidad
            $table->decimal('cost_at_order', 8, 2)->default(0.00);

            // 'unit' almacena la unidad de medida utilizada ('Kg', 'Lt', 'Unidad', etc.)
            // Esto asegura que la cantidad sea clara para el historial.
            $table->string('unit', 20)->default('Unidad');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
