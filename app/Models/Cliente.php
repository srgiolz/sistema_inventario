<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_cliente',
        'ci_nit',
        'nombre',
        'apellido',
        'sexo',
        'ciudad',
        'direccion',
        'telefono',
        'id_medico',
        'id_diagnostico'
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'id_medico');
    }

    public function diagnostico()
    {
        return $this->belongsTo(Diagnostico::class, 'id_diagnostico');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }
}
