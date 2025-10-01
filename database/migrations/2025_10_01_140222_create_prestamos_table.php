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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventarios');
            $table->foreignId('usuario_inventario_id')->constrained('usuario_inventarios');
            $table->dateTime('fecha_prestamo');
            $table->dateTime('fecha_devolucion')->nullable();
            $table->string('estado')->default('activo');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
