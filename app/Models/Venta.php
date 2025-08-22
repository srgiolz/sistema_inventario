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
    'tipo_pago_id',
    'descuento_total',
    'total',
    'con_factura',
    'estado',        // nuevo
    'observacion',   // nuevo
];

    protected $casts = [
        'con_factura' => 'boolean',
        'fecha' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function detalles()
    {
        // en detalle_ventas la FK es 'venta_id'
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function tipoPago()
    {
        // relación correcta hacia tipos_pago por 'tipo_pago_id'
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }
}

