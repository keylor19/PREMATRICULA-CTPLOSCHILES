<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carreras';

    protected $fillable = [
        'modalidad_id',
        'nivel_id',
        'nombre',
        'capacidad',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    public function prematriculas()
    {
        return $this->hasMany(Prematricula::class);
    }

    public function cuposOcupados(): int
    {
        return Prematricula::where('carrera_id', $this->id)
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->count();
    }

    public function cuposDisponibles(): int
    {
        return max(0, $this->capacidad - $this->cuposOcupados());
    }

    public function estaLlena(): bool
    {
        return $this->cuposDisponibles() === 0;
    }
}