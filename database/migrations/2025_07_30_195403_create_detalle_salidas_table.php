<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detalle_salidas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salida_id');
            $table->unsignedBigInteger('id_producto');
            $table->integer('cantidad');
            $table->timestamps();

            // Claves foráneas
            $table->foreign('salida_id')->references('id')->on('salidas')->onDelete('cascade');
            $table->foreign('id_producto')->references('id')->on('productos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_salidas');
    }
};
