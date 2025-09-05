<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Traspaso extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_origen_id',
        'sucursal_destino_id',
        'fecha',
        'observacion',
        'tipo',
        'estado',
        'fecha_confirmacion',
        'usuario_confirma_id',   // corregido
        'fecha_recepcion',       // nuevo
        'usuario_recepciona_id', // nuevo
        'motivo_anulacion',      // nuevo
    ];

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }

    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleTraspaso::class, 'traspaso_id');
    }
}


