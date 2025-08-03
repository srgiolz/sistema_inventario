<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->string('tipo')->nullable()->after('fecha');
        });
    }

    public function down()
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};

