<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('prestamos', function (Blueprint $table) {
            if (!Schema::hasColumn('prestamos', 'obra_titulo')) {
                $table->string('obra_titulo')->nullable()->after('partitura_id');
            }
        });
    }

    public function down(): void {
        Schema::table('prestamos', function (Blueprint $table) {
            if (Schema::hasColumn('prestamos', 'obra_titulo')) {
                $table->dropColumn('obra_titulo');
            }
        });
    }
};