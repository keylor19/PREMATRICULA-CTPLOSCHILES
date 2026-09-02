<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Familiar extends Model
{
    protected $table = 'familiares';

    protected $fillable = [
        'estudiante_id',
        'tipo',
        'nombre_completo',
        'cedula',
        'telefono_principal',
        'telefono_secundario',
        'email',
        'ocupacion',
        'direccion',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }
}