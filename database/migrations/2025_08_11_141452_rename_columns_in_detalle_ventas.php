<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar columnas en detalle_ventas
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->renameColumn('id_venta', 'venta_id');
            $table->renameColumn('id_producto', 'producto_id');
        });
    }

    public function down(): void
    {
        // Deshacer los cambios en detalle_ventas
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->renameColumn('venta_id', 'id_venta');
            $table->renameColumn('producto_id', 'id_producto');
        });
    }
};

