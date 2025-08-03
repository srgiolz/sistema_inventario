<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleEntrada extends Model
{
    use HasFactory;

    protected $table = 'detalle_entradas';

    protected $fillable = [
        'entrada_id',
        'id_producto',
        'cantidad',
        'precio_unitario',
    ];

    // Relaciones (opcional, pero recomendado)
    public function entrada()
    {
        return $this->belongsTo(Entrada::class, 'entrada_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
