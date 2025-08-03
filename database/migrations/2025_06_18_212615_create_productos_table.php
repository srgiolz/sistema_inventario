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
    Schema::create('productos', function (Blueprint $table) {
        $table->id();
        $table->string('item_codigo')->unique();
        $table->string('cod_barra')->nullable();
        $table->string('descripcion');
        $table->string('linea')->nullable();
        $table->string('familia')->nullable();
        $table->string('unidad_medida')->nullable();
        $table->string('talla')->nullable();
        $table->string('modelo')->nullable();
        $table->string('puntera')->nullable();
        $table->string('color')->nullable();
        $table->string('compresion')->nullable();
        $table->string('categoria')->nullable();
        $table->decimal('precio_costo', 10, 2)->nullable();
        $table->decimal('precio_venta', 10, 2)->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
