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
    Schema::create('toma_inventarios', function (Blueprint $table) {
        $table->id();
        $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade');
        $table->foreignId('id_sucursal')->constrained('sucursales')->onDelete('cascade');
        $table->integer('cantidad_contada');
        $table->integer('cantidad_sistema');
        $table->integer('diferencia');
        $table->date('fecha');
        $table->string('observacion')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toma_inventarios');
    }
};
