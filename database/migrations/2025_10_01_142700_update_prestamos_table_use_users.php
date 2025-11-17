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
        Schema::table('prestamos', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['usuario_inventario_id']);
            
            // Drop the old column
            $table->dropColumn('usuario_inventario_id');
            
            // Add the new user_id column with foreign key constraint
            $table->foreignId('user_id')->constrained('users')->after('inventario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Drop the new column
            $table->dropColumn('user_id');
            
            // Add back the old column and foreign key
            $table->foreignId('usuario_inventario_id')->constrained('usuario_inventarios');
        });
    }
};