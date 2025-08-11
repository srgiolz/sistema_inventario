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
        return $this->hasMany(Inventario::class, 'sucursal_id');  // Cambié 'id_sucursal' por 'sucursal_id'
    }

    public function entradas()
    {
        return $this->hasMany(Entrada::class, 'sucursal_id');  // Cambié 'id_sucursal' por 'sucursal_id'
    }

    public function salidas()
    {
        return $this->hasMany(Salida::class, 'sucursal_id');  // Cambié 'id_sucursal' por 'sucursal_id'
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'sucursal_id');
    }

    public function tomaInventarios()
    {
        return $this->hasMany(TomaInventario::class, 'sucursal_id');  // Cambié 'id_sucursal' por 'sucursal_id'
    }
}

