<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Traspaso extends Model
{
    use HasFactory;

    protected $fillable = [
        'de_sucursal',
        'a_sucursal',
        'fecha',
        'observacion',
        'tipo',
        'estado',
        'fecha_confirmacion',
        'usuario_confirmacion_id'
    ];

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'de_sucursal');
    }

    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursal::class, 'a_sucursal');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleTraspaso::class, 'traspaso_id');
    }
}


