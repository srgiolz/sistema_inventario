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
    Schema::create('traspasos', function (Blueprint $table) {
        $table->bigIncrements('id');

        $table->unsignedBigInteger('de_sucursal');
        $table->unsignedBigInteger('a_sucursal');

        $table->date('fecha');
        $table->string('observacion')->nullable();

        $table->enum('tipo', ['abastecimiento', 'sucursal'])->default('sucursal');
        $table->enum('estado', ['pendiente', 'confirmado', 'rechazado'])->default('pendiente');

        $table->timestamp('fecha_confirmacion')->nullable();
        $table->unsignedBigInteger('usuario_confirmacion_id')->nullable();

        $table->timestamps();

        // Relaciones foráneas
        $table->foreign('de_sucursal')->references('id')->on('sucursales')->onDelete('cascade');
        $table->foreign('a_sucursal')->references('id')->on('sucursales')->onDelete('cascade');
        $table->foreign('usuario_confirmacion_id')->references('id')->on('users')->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traspasos');
    }
};
