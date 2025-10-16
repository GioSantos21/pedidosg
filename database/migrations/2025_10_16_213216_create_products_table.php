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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Relación con Categorías
            $table->string('name')->unique();
            $table->string('unit')->nullable()->comment('Ej. Unidad, Kg, Paquete'); // Unidad de medida para el pedido
            $table->decimal('cost', 8, 2)->nullable(); // Costo de producción (opcional, para reportes internos)
            $table->boolean('is_active')->default(true); // Para activar/desactivar un producto del catálogo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
