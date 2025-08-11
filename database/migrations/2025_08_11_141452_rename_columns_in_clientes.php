<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar columnas en clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->renameColumn('id_medico', 'medico_id');
            $table->renameColumn('id_diagnostico', 'diagnostico_id');
        });
    }

    public function down(): void
    {
        // Deshacer los cambios en clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->renameColumn('medico_id', 'id_medico');
            $table->renameColumn('diagnostico_id', 'id_diagnostico');
        });
    }
};
