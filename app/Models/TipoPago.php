<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model
{
    use HasFactory;

    // Si la tabla no se llama 'tipos_pago', puedes especificar el nombre de la tabla aquí
    protected $table = 'tipos_pago';

    // Relación con las ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'tipo_pago_id');
    }
}
