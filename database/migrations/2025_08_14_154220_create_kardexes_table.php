<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kardex', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('producto_id');
            $table->decimal('cantidad', 15, 2);
            $table->string('tipo_movimiento'); // entrada, salida, venta, traspaso
            $table->decimal('stock_final', 15, 2);
            $table->decimal('precio', 15, 2)->default(0);
            $table->string('documento_tipo'); // Ej: "venta", "entrada"
            $table->unsignedBigInteger('documento_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamp('fecha');
            $table->timestamps();

            // Claves foráneas opcionales
            $table->foreign('sucursal_id')->references('id')->on('sucursales');
            $table->foreign('producto_id')->references('id')->on('productos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};
