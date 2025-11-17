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
        Schema::create('contribuciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->constrained('autores');
            $table->foreignId('obra_id')->constrained('obras');
            $table->foreignId('tipo_contribucion_id')->constrained('tipo_contribuciones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribuciones');
    }
};
