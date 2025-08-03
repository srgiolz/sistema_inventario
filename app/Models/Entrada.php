<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entrada extends Model
{
    use HasFactory;

    protected $fillable = ['id_sucursal', 'fecha', 'tipo', 'observacion'];
    
    public function detalles()
    {
        return $this->hasMany(DetalleEntrada::class, 'entrada_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function traspaso()
    {
        return $this->hasOne(Traspaso::class, 'id_entrada');
    }
}
