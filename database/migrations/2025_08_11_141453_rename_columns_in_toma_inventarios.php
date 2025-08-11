<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar columnas en toma_inventarios
        Schema::table('toma_inventarios', function (Blueprint $table) {
            $table->renameColumn('id_producto', 'producto_id');
            $table->renameColumn('id_sucursal', 'sucursal_id');
        });
    }

    public function down(): void
    {
        // Deshacer los cambios en toma_inventarios
        Schema::table('toma_inventarios', function (Blueprint $table) {
            $table->renameColumn('producto_id', 'id_producto');
            $table->renameColumn('sucursal_id', 'id_sucursal');
        });
    }
};
