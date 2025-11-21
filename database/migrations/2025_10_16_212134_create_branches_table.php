<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// NOTA: Asumo que has renombrado el archivo con un timestamp posterior,
// por ejemplo: 2025_10_16_212133_create_branches_table.php

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear la tabla branches (esta no tiene dependencias)
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('external_code')->nullable();
            $table->string('abbreviation', 10)->nullable()->unique();
            $table->timestamps();
        });

        // 2. Añadir el campo branch_id a la tabla users (AHORA LA TABLA USERS YA EXISTE)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')
                  ->nullable()
                  ->after('id')
                  ->constrained() // Esto crea el índice y la FK a la tabla 'branches'
                  ->onDelete('set null'); // Opción en caso de que una sucursal sea eliminada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la clave foránea de users primero
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        // Luego eliminar la tabla branches
        Schema::dropIfExists('branches');
    }
};
