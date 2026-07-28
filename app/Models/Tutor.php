<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $table = 'tutores';

    protected $fillable = [
        'user_id',
        'nombre_completo',
        'relacion',
        'cedula',
        'telefono_principal',
        'telefono_secundario',
        'email',
        'ocupacion',
        'direccion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prematricula()
    {
        return $this->hasOne(Prematricula::class);
    }
}