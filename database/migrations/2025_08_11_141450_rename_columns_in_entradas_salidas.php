<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar columnas en entradas
        Schema::table('entradas', function (Blueprint $table) {
            $table->renameColumn('id_sucursal', 'sucursal_id');
        });

        // Renombrar columnas en salidas
        Schema::table('salidas', function (Blueprint $table) {
            $table->renameColumn('id_sucursal', 'sucursal_id');
        });
    }

    public function down(): void
    {
        // Deshacer los cambios en entradas
        Schema::table('entradas', function (Blueprint $table) {
            $table->renameColumn('sucursal_id', 'id_sucursal');
        });

        // Deshacer los cambios en salidas
        Schema::table('salidas', function (Blueprint $table) {
            $table->renameColumn('sucursal_id', 'id_sucursal');
        });
    }
};

