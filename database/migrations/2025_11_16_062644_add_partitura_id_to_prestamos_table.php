<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            if (!Schema::hasColumn('prestamos', 'partitura_id')) {
                $table->unsignedBigInteger('partitura_id')
                      ->nullable()
                      ->after('inventario_id') // si te da error, quita este "after"
                      ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            if (Schema::hasColumn('prestamos', 'partitura_id')) {
                $table->dropColumn('partitura_id'); // al borrar la columna, el índice se cae solo
            }
        });
    }
};