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
        'medico_id',  // Cambié 'id_medico' por 'medico_id'
        'diagnostico_id'  // Cambié 'id_diagnostico' por 'diagnostico_id'
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');  // Cambié 'id_medico' por 'medico_id'
    }

    public function diagnostico()
    {
        return $this->belongsTo(Diagnostico::class, 'diagnostico_id');  // Cambié 'id_diagnostico' por 'diagnostico_id'
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }
}

