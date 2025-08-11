<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas'; // nombre exacto de la tabla

    protected $fillable = [
        'venta_id',  // Cambié 'id_venta' por 'venta_id'
        'producto_id',  // Cambié 'id_producto' por 'producto_id'
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');  // Cambié 'id_venta' por 'venta_id'
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');  // Cambié 'id_producto' por 'producto_id'
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');  // Cambié 'id_venta' por 'venta_id'
    }
}
