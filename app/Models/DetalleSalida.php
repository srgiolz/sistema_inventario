<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleSalida extends Model
{
    use HasFactory;

    protected $table = 'detalle_salidas';

    protected $fillable = [
        'salida_id',
        'producto_id',
        'cantidad',
    ];

    public function salida()
    {
        return $this->belongsTo(Salida::class, 'salida_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
