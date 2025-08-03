<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('clientes', function (Blueprint $table) {
        $table->id();
        $table->enum('tipo_cliente', ['particular', 'paciente']);
        $table->string('ci_nit')->nullable();
        $table->string('nombre');
        $table->string('apellido')->nullable();
        $table->string('sexo')->nullable();
        $table->string('ciudad')->nullable();
        $table->string('direccion')->nullable();
        $table->string('telefono')->nullable();
        $table->foreignId('id_medico')->nullable()->constrained('medicos')->onDelete('set null');
        $table->foreignId('id_diagnostico')->nullable()->constrained('diagnosticos')->onDelete('set null');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
