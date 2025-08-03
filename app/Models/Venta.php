<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'sucursal_id',
        'fecha',
        'tipo_pago',
        'descuento_total',
        'total',
        'con_factura'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    // Cambiar a "detalles" para coincidir con la vista
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta');
    }
}

