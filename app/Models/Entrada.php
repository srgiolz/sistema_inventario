<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entrada extends Model
{
    use HasFactory;

    protected $fillable = ['sucursal_id', 'fecha', 'tipo', 'observacion'];  // Cambié 'id_sucursal' por 'sucursal_id'
    
    public function detalles()
    {
        return $this->hasMany(DetalleEntrada::class, 'entrada_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');  // Cambié 'id_sucursal' por 'sucursal_id'
    }

    public function traspaso()
    {
        return $this->hasOne(Traspaso::class, 'id_entrada');
    }
}
