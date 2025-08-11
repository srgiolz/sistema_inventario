<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar columnas en productos
        Schema::table('productos', function (Blueprint $table) {
            $table->renameColumn('item_codigo', 'codigo_item');
            $table->renameColumn('cod_barra', 'codigo_barra');
        });

        // Renombrar columnas en inventarios
        Schema::table('inventarios', function (Blueprint $table) {
            $table->renameColumn('id_producto', 'producto_id');
            $table->renameColumn('id_sucursal', 'sucursal_id');
        });
    }

    public function down(): void
    {
        // Deshacer los cambios en productos
        Schema::table('productos', function (Blueprint $table) {
            $table->renameColumn('codigo_item', 'item_codigo');
            $table->renameColumn('codigo_barra', 'cod_barra');
        });

        // Deshacer los cambios en inventarios
        Schema::table('inventarios', function (Blueprint $table) {
            $table->renameColumn('producto_id', 'id_producto');
            $table->renameColumn('sucursal_id', 'id_sucursal');
        });
    }
};

