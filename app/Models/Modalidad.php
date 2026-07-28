<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    protected $table = 'modalidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function niveles()
    {
        return $this->hasMany(Nivel::class);
    }

    public function prematriculas()
    {
        return $this->hasMany(Prematricula::class);
    }

    public function docentes()
    {
        return $this->belongsToMany(User::class, 'docente_modalidad');
    }
}