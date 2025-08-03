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
    Schema::create('detalle_ventas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('id_venta')->constrained('ventas')->onDelete('cascade');
        $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade');
        $table->integer('cantidad');
        $table->decimal('precio_unitario', 10, 2);
        $table->decimal('descuento', 10, 2)->default(0);
        $table->decimal('subtotal', 10, 2);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
