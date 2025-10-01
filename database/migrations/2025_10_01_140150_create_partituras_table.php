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
        Schema::create('partituras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras');
            $table->string('tipo_partitura');
            $table->string('formato');
            $table->integer('numero_paginas')->nullable();
            $table->string('idioma')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partituras');
    }
};
