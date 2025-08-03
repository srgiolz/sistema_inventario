<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_codigo', 'cod_barra', 'descripcion', 'linea', 'familia', 'unidad_medida',
        'talla', 'modelo', 'puntera', 'color', 'compresion', 'categoria',
        'precio_costo', 'precio_venta'
    ];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_producto');
    }

    public function entradas()
    {
        return $this->hasMany(Entrada::class, 'id_producto');
    }

    public function salidas()
    {
        return $this->hasMany(Salida::class, 'id_producto');
    }

    public function traspasos()
    {
        return $this->hasMany(Traspaso::class, 'id_producto');
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto');
    }

    public function tomaInventarios()
    {
        return $this->hasMany(TomaInventario::class, 'id_producto');
    }
}
