<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TomaInventario extends Model
{
    use HasFactory;

    protected $table = 'toma_inventarios';

    protected $fillable = [
        'producto_id',  // Cambié 'producto_id' por 'producto_id'
        'sucursal_id',  // Cambié 'sucursal_id' por 'sucursal_id'
        'cantidad_contada',
        'cantidad_sistema',
        'diferencia',
        'fecha',
        'observacion'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');  // Cambié 'producto_id' por 'producto_id'
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');  // Cambié 'sucursal_id' por 'sucursal_id'
    }
}

