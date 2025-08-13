<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Salida extends Model
{
    use HasFactory;

    protected $fillable = ['sucursal_id', 'fecha', 'motivo', 'observacion', 'tipo'];  // Cambié 'sucursal_id' por 'sucursal_id'

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');  // Cambié 'sucursal_id' por 'sucursal_id'
    }

    public function detalles()
    {
        return $this->hasMany(DetalleSalida::class, 'salida_id');
    }
}

