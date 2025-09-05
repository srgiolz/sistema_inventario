<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Salida extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'tipo',
        'fecha',
        'motivo',
        'observacion',
        'estado',
        'fecha_confirmacion',
        'usuario_confirma_id',
        'motivo_anulacion'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleSalida::class, 'salida_id');
    }

    public function usuarioConfirma()
    {
        return $this->belongsTo(User::class, 'usuario_confirma_id');
    }
}

