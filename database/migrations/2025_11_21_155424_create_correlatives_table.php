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
        Schema::create('correlatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->unique()->constrained()->onDelete('cascade');
            $table->string('prefix', 10);
            $table->unsignedBigInteger('initial')->default(1);
            $table->unsignedBigInteger('final')->default(99999999);
            $table->unsignedBigInteger('counter')->default(0)->comment('El último número utilizado');
            $table->unsignedBigInteger('counter_record')->default(0)->comment('Contador de registros');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correlatives');
    }
};
