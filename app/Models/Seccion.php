<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'secciones';
    protected $fillable = [
        'nivel_id',
        'nombre',
        'numero',
        'capacidad',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    public function prematriculas()
    {
        return $this->hasMany(Prematricula::class);
    }
    public function talleres()
{
    return $this->hasMany(SeccionTaller::class);
}

public function tallerGrupoA()
{
    return $this->hasOne(SeccionTaller::class)->where('grupo', 'A');
}

public function tallerGrupoB()
{
    return $this->hasOne(SeccionTaller::class)->where('grupo', 'B');
}
}