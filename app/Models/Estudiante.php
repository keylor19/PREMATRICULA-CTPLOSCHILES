<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
   protected $fillable = [
    'nombre',
    'apellido',
    'cedula',
    'fecha_nacimiento',
    'genero',
    'nacionalidad',
    'email_mep',
    'email_personal',
    'telefono',
    'direccion',
    'condicion_salud',
    'adecuacion',
    'tipo_discapacidad',
    'boleta_ubicacion',
    'nivel_funcionamiento',
];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function prematricula()
    {
        return $this->hasOne(Prematricula::class);
    }

    public function familiares()
    {
        return $this->hasMany(Familiar::class);
    }
}