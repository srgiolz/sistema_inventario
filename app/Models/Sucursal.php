<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sucursal extends Model
{
    
    use HasFactory;
    protected $table = 'sucursales';


    protected $fillable = ['nombre', 'direccion'];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_sucursal');
    }

    public function entradas()
    {
        return $this->hasMany(Entrada::class, 'id_sucursal');
    }

    public function salidas()
    {
        return $this->hasMany(Salida::class, 'id_sucursal');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'sucursal_id');
    }

    public function tomaInventarios()
    {
        return $this->hasMany(TomaInventario::class, 'id_sucursal');
    }
}

