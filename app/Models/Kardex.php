<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
    protected $table = 'kardex'; // nombre de la tabla
    protected $fillable = [
        'sucursal_id',
        'producto_id',
        'cantidad',
        'tipo_movimiento',
        'stock_final',
        'precio',
        'documento_tipo',
        'documento_id',
        'usuario_id',
        'fecha',
        'doc_ref',   
    ];

    // Relaciones opcionales
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
