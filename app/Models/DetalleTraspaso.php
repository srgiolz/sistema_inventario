<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleTraspaso extends Model
{
    protected $table = 'detalle_traspasos';

    protected $fillable = [
        'traspaso_id',
        'producto_id',
        'cantidad',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function traspaso()
    {
        return $this->belongsTo(Traspaso::class);
    }
}
