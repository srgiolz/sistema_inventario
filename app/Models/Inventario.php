<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = ['producto_id', 'sucursal_id', 'cantidad'];  // Cambié 'producto_id' por 'producto_id' y 'sucursal_id' por 'sucursal_id'

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');  // Cambié 'producto_id' por 'producto_id'
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');  // Cambié 'sucursal_id' por 'sucursal_id'
    }
}

