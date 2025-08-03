<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TomaInventario extends Model
{
    use HasFactory;

    protected $table = 'toma_inventarios';

    protected $fillable = [
        'id_producto',
        'id_sucursal',
        'cantidad_contada',
        'cantidad_sistema',
        'diferencia',
        'fecha',
        'observacion'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
}
