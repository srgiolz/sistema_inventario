<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar columnas en detalle_entradas
        Schema::table('detalle_entradas', function (Blueprint $table) {
            $table->renameColumn('id_producto', 'producto_id');
        });

        // Renombrar columnas en detalle_salidas
        Schema::table('detalle_salidas', function (Blueprint $table) {
            $table->renameColumn('id_producto', 'producto_id');
        });
    }

    public function down(): void
    {
        // Deshacer los cambios en detalle_entradas
        Schema::table('detalle_entradas', function (Blueprint $table) {
            $table->renameColumn('producto_id', 'id_producto');
        });

        // Deshacer los cambios en detalle_salidas
        Schema::table('detalle_salidas', function (Blueprint $table) {
            $table->renameColumn('producto_id', 'id_producto');
        });
    }
};
