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
    Schema::create('medicos', function (Blueprint $table) {
        $table->id();
        $table->string('codigo_medico')->unique();
        $table->string('nombre');
        $table->string('especialidad')->nullable();
        $table->string('direccion')->nullable();
        $table->string('email')->nullable();
        $table->string('telefono')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};
