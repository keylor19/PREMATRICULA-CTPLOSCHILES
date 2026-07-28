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
    'direccion',
    'condicion_salud',
    'adecuacion',
];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function prematricula()
    {
        return $this->hasOne(Prematricula::class);
    }
}