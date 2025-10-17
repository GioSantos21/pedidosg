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
        Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('branch_id')->constrained(); // Sucursal que pide
        $table->foreignId('user_id')->constrained(); // Gerente que registra
        $table->enum('status', ['Pendiente', 'Confirmado', 'Anulado'])->default('Pendiente');
        $table->text('notes')->nullable();
        $table->timestamp('requested_at')->useCurrent();
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
