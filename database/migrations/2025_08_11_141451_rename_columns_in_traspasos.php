<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar columnas en traspasos
        Schema::table('traspasos', function (Blueprint $table) {
            $table->renameColumn('de_sucursal', 'sucursal_origen_id');
            $table->renameColumn('a_sucursal', 'sucursal_destino_id');
            $table->renameColumn('usuario_confirmacion_id', 'usuario_confirma_id');
        });
    }

    public function down(): void
    {
        // Deshacer los cambios en traspasos
        Schema::table('traspasos', function (Blueprint $table) {
            $table->renameColumn('sucursal_origen_id', 'de_sucursal');
            $table->renameColumn('sucursal_destino_id', 'a_sucursal');
            $table->renameColumn('usuario_confirma_id', 'usuario_confirmacion_id');
        });
    }
};

