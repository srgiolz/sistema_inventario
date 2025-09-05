<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entrada extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'fecha',
        'tipo',
        'observacion',
        'estado',                // 🔹 ahora sí se podrá actualizar
        'fecha_confirmacion',    // 🔹 ahora sí se podrá actualizar
        'usuario_confirma_id',   // 🔹 ahora sí se podrá actualizar
        'motivo_anulacion',      // 🔹 ahora sí se podrá actualizar
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_confirmacion' => 'datetime',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleEntrada::class, 'entrada_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function traspaso()
    {
        return $this->hasOne(Traspaso::class, 'id_entrada');
    }
}

