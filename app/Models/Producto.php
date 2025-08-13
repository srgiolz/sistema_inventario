<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_item', 'codigo_barra', 'descripcion', 'linea', 'familia', 'unidad_medida',
        'talla', 'modelo', 'puntera', 'color', 'compresion', 'categoria',
        'precio_costo', 'precio_venta'
    ];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'producto_id');  // Cambié 'producto_id' por 'producto_id'
    }

    public function entradas()
    {
        return $this->hasMany(Entrada::class, 'producto_id');  // Cambié 'producto_id' por 'producto_id'
    }

    public function salidas()
    {
        return $this->hasMany(Salida::class, 'producto_id');  // Cambié 'producto_id' por 'producto_id'
    }

    public function traspasos()
    {
        return $this->hasMany(Traspaso::class, 'producto_id');  // Cambié 'producto_id' por 'producto_id'
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');  // Cambié 'producto_id' por 'producto_id'
    }

    public function tomaInventarios()
    {
        return $this->hasMany(TomaInventario::class, 'producto_id');  // Cambié 'producto_id' por 'producto_id'
    }
}

