<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_medico',
        'nombre',
        'especialidad',
        'direccion',
        'email',
        'telefono'
    ];
}

