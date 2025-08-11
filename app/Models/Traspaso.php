<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Traspaso extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_origen_id',  // Cambié 'de_sucursal' por 'sucursal_origen_id'
        'sucursal_destino_id',  // Cambié 'a_sucursal' por 'sucursal_destino_id'
        'fecha',
        'observacion',
        'tipo',
        'estado',
        'fecha_confirmacion',
        'usuario_confirmacion_id'
    ];

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');  // Cambié 'de_sucursal' por 'sucursal_origen_id'
    }

    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');  // Cambié 'a_sucursal' por 'sucursal_destino_id'
    }

    public function detalles()
    {
        return $this->hasMany(DetalleTraspaso::class, 'traspaso_id');
    }
}


