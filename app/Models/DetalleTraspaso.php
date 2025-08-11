<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleTraspaso extends Model
{
    protected $table = 'detalle_traspasos';

    protected $fillable = [
        'traspaso_id',
        'producto_id',  // Cambié 'id_producto' por 'producto_id'
        'cantidad',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');  // Cambié 'id_producto' por 'producto_id'
    }

    public function traspaso()
    {
        return $this->belongsTo(Traspaso::class, 'traspaso_id');
    }
}

